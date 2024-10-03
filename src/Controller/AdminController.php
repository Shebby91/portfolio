<?php

namespace App\Controller;

use App\Entity\File;
use App\Service\HealthcheckService;
use App\Repository\UserRepository;
use App\Service\AwsS3Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ImageUploadFormType;
use App\Repository\FileRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function admin(Request $request, UserRepository $userRepository, PaginatorInterface $paginator, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $users = $userRepository->findAll();
        $users = $paginator->paginate(
            // Doctrine Query, not results
            $users,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            20
        );

        $csrfToken = $csrfTokenManager->getToken('action_id')->getValue();

        return $this->render('admin/admin.html.twig', [
            'title' => 'User',
            'users' => $users,
            'user' => $user,
            'csrf_token' => $csrfToken
        ]);
    }

    #[Route('/admin/healthcheck', name: 'app_admin_healthcheck')]
    public function healthcheck(HealthcheckService $checkHealth): Response
    {
        $user = $this->getUser();
        return $this->render('admin/admin_healthcheck.html.twig', [
            'title' => 'Healthcheck',
            'connection' => $checkHealth->checkDatabaseConnection(),
            's3Status' => $checkHealth->checkS3Connection(),
            'user' => $user
        ]);
    }

    #[Route('/admin/files', name: 'app_admin_files')]
    public function adminFiles(Request $request, AwsS3Service $s3, PaginatorInterface $paginator, EntityManagerInterface $entityManager, FileRepository $fileRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $profileImg = $user->getProfileImg();
        
        $bucketName = 'my-bucket';

        $s3->checkOrCreateBucket($bucketName);

        $files = $fileRepository->findAll();;
        $files = $paginator->paginate(
            // Doctrine Query, not results
            $files,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            7
        );

        $form = $this->createForm(ImageUploadFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                // Speichere die Datei temporär (optional)
                $file->move('/tmp', $fileName);
        
                // Lade die Datei in den S3 Bucket hoch
                
                $path = $s3->uploadFile($bucketName, $fileName, '/tmp/' . $fileName); // Übergebe den Bucket-Namen, den Key und den Dateipfad
                $uploadedFile = new File();
                $uploadedFile->setFilePath($path);
                $uploadedFile->setLastModified(new DateTime());
                $uploadedFile->setFileName($fileName);
                $uploadedFile->setUser($this->getUser());
                $entityManager->persist($uploadedFile);
                if (is_Null($profileImg)) {
                     $user->setProfileImg($uploadedFile);
                }
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', "Datei '$fileName' wurde erfolgreich hochgeladen.");
                return $this->redirectToRoute('app_admin_files');
            }
        }

        return $this->render('admin/admin_files.html.twig', [
            'files' => $files,
            'title' => 'Files',
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/admin/files/download/{key}', name: 'app_admin_files_download')]
    public function adminFilesDownload(AwsS3Service $s3, String $key): Response
    {
        $this->getUser();
        $bucketName = 'my-bucket'; // Ersetze dies durch deinen Bucket-Namen
        $savePath = '/tmp/' . $key; // Temporärer Speicherort für die Datei
    
        $downloadResult = $s3->downloadFile($bucketName, $key, $savePath);
    
        if (strpos($downloadResult, 'erfolgreich') !== false) {
            return $this->file($savePath); // Symfony liefert die Datei zurück
        }
    
        return new Response('Datei konnte nicht heruntergeladen werden: ' . $downloadResult, Response::HTTP_BAD_REQUEST);
    }

    #[Route('/admin/files/delete/{id}', name: 'app_admin_files_delete')]
    public function adminFilesDelete(AwsS3Service $s3, Int $id, FileRepository $fileRepository, EntityManagerInterface $entityManager): Response
    {
     
        /** @var User $user */
        $user = $this->getUser();
        //TODO: Add bucketName to entity File
        $file = $fileRepository->findOneBy(['id' => $id]);
        $key = $file->getFilename();
        $bucketName = 'my-bucket'; // Ersetze dies durch deinen Bucket-Namen
        $deleteResult = $s3->deleteFile($bucketName, $key); // Stelle sicher, dass diese Methode im Service vorhanden ist.

        if (strpos($deleteResult, 'erfolgreich') !== false) {
            $user->setProfileImg(null);
            $entityManager->persist($user);
            $entityManager->remove($file);
            $entityManager->flush();
            $this->addFlash('success', "Datei '$key' wurde erfolgreich gelöscht.");
        } else {
            $this->addFlash('error', "Fehler beim Löschen der Datei: " . $deleteResult);
        }
    
        return $this->redirectToRoute('app_admin_files');
    }

    #[Route('/admin/endpoint/reset/2fa', name: 'app_reset_2fa')]
    public function reset2fa(Request $request, UserRepository $userRepository, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager, ValidatorInterface $validator): Response
    {
        $csrfToken = $request->headers->get('X-CSRF-TOKEN');

        // Validierung des Tokens
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('action_id', $csrfToken))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['id'])) {
            return new JsonResponse(['error' => 'Ungültige oder fehlende ID'], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        /** @var User $user */
        $user = $userRepository->findOneBy(['id' => $data['id']]);

        $user->setIsTwoFactorEnabled(false);
        $user->setGoogleAuthenticatorSecret(NULL);
        $em->persist($user);
        $em->flush();

        // Hier führst du deine Logik aus, z.B. Daten verarbeiten
        $result = ['message' => 'Deactivated Auth_2 successfully for '.$user->getEmail() ];

        return new JsonResponse($result);
    }

    #[Route('/admin/endpoint/reset/verified', name: 'app_reset_email_verified')]
    public function resetVerified(Request $request, UserRepository $userRepository, CsrfTokenManagerInterface $csrfTokenManager, EntityManagerInterface $em): Response
    {

        $csrfToken = $request->headers->get('X-CSRF-TOKEN');

        // Validierung des Tokens
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('action_id', $csrfToken))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['id'])) {
            return new JsonResponse(['error' => 'Ungültige oder fehlende ID'], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        /** @var User $user */
        $user = $userRepository->findOneBy(['id' => $data['id']]);

        $user->setIsVerified(false);

        $em->persist($user);
        $em->flush();

        // Hier führst du deine Logik aus, z.B. Daten verarbeiten
        $result = ['message' => 'Deactivated Auth_1 successfully for '.$user->getEmail() ];

        return new JsonResponse($result);
    }
}
