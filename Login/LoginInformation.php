<?php

namespace Ibrows\SyliusShopBundle\Login;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

class LoginInformation implements LoginInformationInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CsrfProviderInterface
     */
    protected $csrfProvider;

    /**
     * @var string
     */
    protected $lastUsername;

    /**
     * @var string
     */
    protected $error;

    /**
     * @param Request $request
     * @param CsrfProviderInterface $csrfProvider
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(Request $request, CsrfProviderInterface $csrfProvider, SecurityContextInterface $securityContext)
    {
        $this->request = $request;
        $this->csrfProvicer = $csrfProvider;
        $this->securityContext = $securityContext;

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
        return $this->csrfProvicer->generateCsrfToken('authenticate');
    }

    protected function removeInformation()
    {
        $session = $this->request->getSession();

        $session->remove(SecurityContextInterface::LAST_USERNAME);
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
    }

    protected function setInformation()
    {
        $request = $this->request;
        $session = $request->getSession();

        $key = SecurityContextInterface::AUTHENTICATION_ERROR;
        $error = null;

        if ($request->attributes->has($key)) {
            $error = $request->attributes->get($key);
        } elseif ($session->has($key)) {
            $error = $session->get($key);
        }

        if($error && $error instanceof \Exception){
            $error = $error->getMessage();
        }

        $this->error = $error;
        $this->lastUsername = $session->get(SecurityContextInterface::LAST_USERNAME);
    }
}