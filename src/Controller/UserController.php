<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\EditProfileFormType;
use App\Repository\FileRepository;
use App\Repository\UserRepository;
use App\Service\AwsS3Service;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


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
    public function userAlbum(UserRepository $userRepository, EntityManagerInterface $entityManager, FileRepository $fileRepository): Response
    {
        $user = $this->getUser();
        $adminUser = $userRepository->findOneBy(['email' => 'admin@test.com']);
        $fileRepository->findBy(['user' => $adminUser], ['lastModified' => 'DESC'], );

        $images = $fileRepository->findBy(['user' => $adminUser], ['lastModified' => 'DESC'], );



        return $this->render('user/user_album.html.twig', [
            'title' => 'Album',
            'user' => $user,
            'images' => $images 
        ]);
    }

    #[Route('/user/profile', name: 'app_user_profile')]
    public function userProfile(Request $request, AwsS3Service $s3, PaginatorInterface $paginator, EntityManagerInterface $entityManager, FileRepository $fileRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $profileImg = $user->getProfileImg();

        $bucketName = 'my-bucket';

        $s3->checkOrCreateBucket($bucketName);

        $form = $this->createForm(EditProfileFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            $userFormData = $form->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                $file->move('/tmp', $fileName);
                $path = $s3->uploadFile($bucketName, $fileName, '/tmp/' . $fileName); // Übergebe den Bucket-Namen, den Key und den Dateipfad
                $uploadedFile = new File();
                $uploadedFile->setFilePath($path);
                $uploadedFile->setLastModified(new DateTime());
                $uploadedFile->setFileName($fileName);
                $uploadedFile->setUser($this->getUser());
                $entityManager->persist($uploadedFile);
                if (!is_null($profileImg)) {
                    $oldProfileImg = $fileRepository->findOneBy(['id' => $profileImg->getId()]);
                    $s3->deleteFile($bucketName, $oldProfileImg->getFileName()); // Übergebe den Bucket-Namen, den Key und den Dateipfad
                    $entityManager->remove($oldProfileImg);
                }
                $profileImg = $uploadedFile;
            }
            $user->setProfileImg($profileImg);
            $user->setFirstName($userFormData->getFirstName());          
            $user->setLastName($userFormData->getLastName());
            $user->setEmail($userFormData->getEmail());
            $entityManager->persist($user);

            $entityManager->flush();
            //$this->addFlash('success', "Profilbild wurde erfolgreich geändert.");
            return $this->redirectToRoute('app_user_profile');
        }
        
        return $this->render('user/user_profile.html.twig', [
            'title' => 'Profile',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
