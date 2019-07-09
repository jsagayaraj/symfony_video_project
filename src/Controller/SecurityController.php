<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Subscription;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Controller\Traits\SaveSubscription;

class SecurityController extends AbstractController
{
    use SaveSubscription;
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $helper)
    {
        return $this->render('front/login.html.twig', [
            'error' => $helper->getLastAuthenticationError()
        ]);
    }


    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }


    /**
     * @Route("/register/{plan}", name="register", defaults={"plan": null})
     */
    public function register(Request $request, UserPasswordEncoderInterface $password_encoder, SessionInterface $session, $plan)
    {
        //create new user depends upon the plan (free, pro, enterprise) so we get the plan by URL
        if($request->isMethod('GET'))
        {
            // set Session variable so that it is available everywhere
            $session->set('planName', $plan);
            $session->set('planPrice', Subscription::getPlanDataPriceByName($plan));
        }
        
        //registration new user by post method
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            //dd("registering user ...");
            $entityManager = $this->getDoctrine()->getManager();
            $user->setName($request->request->get('user')['name']);
            $user->setName($request->request->get('user')['lastName']);
            $user->setName($request->request->get('user')['email']);
            $password = $password_encoder->encodePassword($user, $request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            //subscription plan
            $date = new \Datetime(); //create date object
            $date->modify('+1 month'); //valid for one month

            $subscription = new Subscription();//create instance of subscription
            $subscription->setValidTo($date); // set valid date to one month for free
            $subscription->setPlan($session->get('planName'));
            if($plan == Subscription::getPlanDataNameByIndex(0))// free plan
            {
                $subscription->setFreePlanUsed(true);
                $subscription->setPaymentStatus('paid');
            }

            $user->setSubscription($subscription);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->loginUserAutomatically($user, $password);

            return $this->redirectToRoute('admin_main_page');

        }
        if($this->isGranted('IS_AUTHENTICATED_REMEMBERED') && $plan == Subscription::getPlanDataNameByIndex(0))//free plan
        {
            //to do save subscripton
            return $this->redirectToRoute('admin_main_page');
        }
        elseif($this->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            return $this->redirectToRoute('payment');
        }
        return $this->render('front/register.html.twig', [
            'form' => $form->createView()
        ]);

    }


    


    //this method helps user login automatically while register as new user
    private function loginUserAutomatically($user, $password)
    {
        $token = new UsernamePasswordToken(
            $user,
            $password,
            'main',
            $user->getRoles()
        );
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }

}
