<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\ImageUploadFormType;
use App\Repository\FileRepository;
use App\Service\AwsS3Service;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function user(): Response
    {
        $user = $this->getUser();
        
        
        return $this->render('user/user.html.twig', [
            'title' => 'User',
            'user' => $user
        ]);
    }

    #[Route('/user/album', name: 'app_user_album')]
    public function userAlbum(): Response
    {
        $user = $this->getUser();
        
        
        return $this->render('user/user_album.html.twig', [
            'title' => 'Album',
            'user' => $user
        ]);
    }

    #[Route('/user/profile', name: 'app_user_profile')]
    public function userProfile(Request $request, AwsS3Service $s3, PaginatorInterface $paginator, EntityManagerInterface $entityManager, FileRepository $fileRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $profileImg = $user->getProfileImg();


        $bucketName = 'my-bucket';
        
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
                $user->setProfileImg($uploadedFile);
                $entityManager->persist($user);
                if (!is_null($profileImg)) {
                    $oldProfileImg = $fileRepository->findOneBy(['id' => $profileImg->getId()]);
                    $s3->deleteFile($bucketName, $oldProfileImg->getFileName()); // Übergebe den Bucket-Namen, den Key und den Dateipfad
                    $entityManager->remove($oldProfileImg);
                }
                $entityManager->flush();
                $this->addFlash('success', "Profilbild wurde erfolgreich geändert.");
                return $this->redirectToRoute('app_admin_files');
            }
        }
        
        return $this->render('user/user_profile.html.twig', [
            'title' => 'Profile',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
