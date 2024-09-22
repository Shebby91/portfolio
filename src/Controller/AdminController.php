<?php

namespace App\Controller;

use App\Service\HealthcheckService;
use App\Repository\UserRepository;
use App\Service\AwsS3Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ImageUploadFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function admin(UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        
        $users = $userRepository->findAll();
        return $this->render('admin/admin.html.twig', [
            'title' => 'User',
            'users' => $users,
            'user' => $user
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
    public function adminFiles(Request $request, AwsS3Service $s3): Response
    {
        $user = $this->getUser();

        $bucketName = 'my-bucket';

        $s3->checkOrCreateBucket($bucketName);

        $files = $s3->listFiles($bucketName);
        
        $form = $this->createForm(ImageUploadFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
        
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                // Speichere die Datei temporär (optional)
                $file->move('/tmp', $fileName);
        
                // Lade die Datei in den S3 Bucket hoch
                $s3->uploadFile($bucketName, $fileName, '/tmp/' . $fileName); // Übergebe den Bucket-Namen, den Key und den Dateipfad
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

    #[Route('/admin/files/delete/{key}', name: 'app_admin_files_delete')]
    public function adminFilesDelete(AwsS3Service $s3, String $key): Response
    {
        
        $this->getUser();
        $bucketName = 'my-bucket'; // Ersetze dies durch deinen Bucket-Namen

        $deleteResult = $s3->deleteFile($bucketName, $key); // Stelle sicher, dass diese Methode im Service vorhanden ist.
    
        if (strpos($deleteResult, 'erfolgreich') !== false) {
            $this->addFlash('success', "Datei '$key' wurde erfolgreich gelöscht.");
        } else {
            $this->addFlash('error', "Fehler beim Löschen der Datei: " . $deleteResult);
        }
    
        return $this->redirectToRoute('app_admin_files');
    }
}
