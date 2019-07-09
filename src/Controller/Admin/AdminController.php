<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Video;
use App\Entity\Category;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeAdminOptionList;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    //update user profile
    /**
     * @Route("/", name="admin_main_page")
     */
    public function index(Request $request, UserPasswordEncoderInterface
     $password_encoder)
    {
        $user = $this->getUser(); // getting information about user from db
        $form = $this->createForm(UserType::class, $user, ['user'=>$user]);
        $form->handleRequest($request);
        $is_invalid = null;

        if($form->isSubmitted() && $form->isValid())
        {
            //if form is valid then we save the changes
            $em = $this->getDoctrine()->getManager();
            $user->setName($request->request->get('user')['name']);
            $user->setLastName( $request->request->get('user')['lastName']);
            $user->setEmail( $request->request->get('user')['email']);

            $password = $password_encoder->encodePassword($user, $request->request->get('user')['password']['first']);
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();

            //exit('valid'); //testing if form is valid it shows the message valid
            $this->addFlash('success', 'Your changes were saved!');

            return $this->redirectToRoute('admin_main_page');
        }
        elseif($request->isMethod('POST'))
        {
            $is_invalid = 'is-invalid';
        }
        return $this->render('admin/my_profile.html.twig', [
            'subscription'=>$this->getUser()->getSubscription(), // this is the relation of OneToOne, the user one who get subscription, it is available in user entity
            'form'=>$form->createView(),
            'is_invalid'=> $is_invalid
        ]);
    }

    /**
     * @Route("/videos", name="videos")
     */
    public function videos( CategoryTreeAdminOptionList $categories)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            //$videos = $this->getDoctrine()->getRepository(Video::class)->findAll();
            $categories->getCategoryList($categories->buildTree());
            $videos = $this->getDoctrine()->getRepository(Video::class)->findBy([],['title' => 'ASC']);

        }else {
            $categories = null;
            $videos = $this->getUser()->getLikedVideos();
        }
        return $this->render('admin/videos.html.twig', [
            'videos'=>$videos,
            'categories' => $categories
        ]);
    }

       

    /**
     * @Route("/cancel-plan", name="cancel_plan")
     */
    public function cancelPlan()
    {
        //find id of the user one who connected
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

        $subscription = $user->getSubscription();
        $subscription->setValidTo(new \DateTime());
        $subscription->setPaymentStatus(null);
        $subscription->setPlan('canceled');
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->persist($subscription);
        $em->flush();

        return $this->redirectToRoute('admin_main_page');
    }

    //delete profile user one who connect already wants to delete son profile
    /**
     * @Route("/delete-account", name="delete_account")
     */
     public function deleteAccount()
     {
         $em = $this->getDoctrine()->getManager();
         $user = $em->getRepository(User::class)->find($this->getUser());

         $em->remove($user);
         $em->flush();

         session_destroy();

         return $this->redirectToRoute('main_page');
         
     }
}

