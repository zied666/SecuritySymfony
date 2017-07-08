<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\ChangePasswordType;
use AppBundle\Form\Model\ChangePassword;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authUtils)
    {
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();
        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->get('doctrine.orm.entity_manager');
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));
            $em->persist($user->setRoles(array("ROLE_USER")));
            $em->flush();
            return $this->redirectToRoute('login');
        }
        return $this->render(
            'security/register.html.twig', array(
                'form' => $form->createView()
            )
        );
    }

    /**
     * @Route("/changePassword", name="change_password")
     */
    public function changePasswordAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $changePasswordModel = new ChangePassword();
        $form = $this->createForm(ChangePasswordType::class, $changePasswordModel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->get('doctrine.orm.entity_manager');
            $user = $this->getUser();
            $user->setPassword($passwordEncoder->encodePassword($user, $changePasswordModel->getNewPassword()));
            $em->persist($user);
            $em->flush();
            return $this->redirect($this->generateUrl('change_password'));
        }
        return $this->render(
            ':security:changePassword.html.twig', array(
                'form' => $form->createView()
            )
        );
    }
}
