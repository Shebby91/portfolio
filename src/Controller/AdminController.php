<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AwsS3Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ImageUploadFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function admin(Request $request, UserRepository $userRepository, AwsS3Service $s3): Response
    {
        
        $this->getUser();

        $bucketName = 'my-bucket';
        $result = $s3->checkOrCreateBucket($bucketName);
        
        $form = $this->createForm(ImageUploadFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
        
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                // Speichere die Datei temporär (optional)
                $file->move('/tmp', $fileName);
        
                // Lade die Datei in den S3 Bucket hoch
                $bucketName = 'my-bucket'; // Der Name deines Buckets
                $s3->uploadFile($bucketName, $fileName, '/tmp/' . $fileName); // Übergebe den Bucket-Namen, den Key und den Dateipfad
        
                return $this->redirectToRoute('app_admin');
            }
        }

        $users = $userRepository->findAll();

        return $this->render('admin/admin.html.twig', [
            'title' => 'Admin',
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/files', name: 'app_admin_files')]
    public function adminFiles(Request $request, UserRepository $userRepository, AwsS3Service $s3): Response
    {
        
        $this->getUser();

        $bucketName = 'my-bucket'; // Der Name deines Buckets
        $files = $s3->listFiles($bucketName);
        //dd($files);
        return $this->render('admin/admin_files.html.twig', [
            'files' => $files,
            'title' => 'Files'
        ]);
    }

    #[Route('/admin/files/download/{key}', name: 'app_admin_files_download')]
    public function adminFilesDownload(Request $request, UserRepository $userRepository, AwsS3Service $s3, String $key): Response
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
    public function adminFilesDelete(Request $request, UserRepository $userRepository, AwsS3Service $s3, String $key): Response
    {
        
        $this->getUser();
        $bucketName = 'my-bucket'; // Ersetze dies durch deinen Bucket-Namen

        $deleteResult = $s3->deleteFile($bucketName, $key); // Stelle sicher, dass diese Methode im Service vorhanden ist.
    
        if (strpos($deleteResult, 'erfolgreich') !== false) {
            $this->addFlash('success', "Datei '$key' wurde erfolgreich gelöscht.");
        } else {
            $this->addFlash('error', "Fehler beim Löschen der Datei: " . $deleteResult);
        }
    
        return $this->render('admin/admin_files.html.twig', [
            'files' => $s3->listFiles($bucketName),
            'title' => 'Files'
        ]);
    }

    #[Route('/test-email-test')]
    public function testEmail(): Response
    {
        $this->getUser();

        return $this->render('email/base_mail.html.twig', ['user' => $this->getUser()]);
    }
}
