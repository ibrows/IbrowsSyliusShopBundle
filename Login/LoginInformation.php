<?php

namespace Ibrows\SyliusShopBundle\Login;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class LoginInformation implements LoginInformationInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $lastUsername;

    /**
     * @var string
     */
    private $error;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param Request $request
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(Request $request, CsrfTokenManagerInterface $csrfTokenManager, TokenStorageInterface $tokenStorage)
    {
        $this->request = $request;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->tokenStorage = $tokenStorage;
        $this->setInformation();
        $this->removeInformation();
    }

    /**
     * @return string|null
     */
    public function getLastUsername()
    {
        return $this->lastUsername;
    }

    /**
     * @return string|null
     */
    public function getAuthenticationError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->csrfTokenManager->getToken('authenticate');
    }

    protected function removeInformation()
    {
        $session = $this->request->getSession();
        $session->remove(Security::LAST_USERNAME);
        $session->remove(Security::AUTHENTICATION_ERROR);
    }

    protected function setInformation()
    {
        $request = $this->request;
        $session = $request->getSession();

        $key = Security::AUTHENTICATION_ERROR;
        $error = null;

        if ($request->attributes->has($key)) {
            $error = $request->attributes->get($key);
        } elseif ($session->has($key)) {
            $error = $session->get($key);
        }

        if ($error && $error instanceof \Exception) {
            $error = $error->getMessage();
        }

        $this->error = $error;
        $this->lastUsername = $session->get(Security::LAST_USERNAME);
    }

    /**
     * @return UserInterface|null
     */
    public function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
