<?php

namespace Pumukit\SecurityBundle\Authentication\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Util\StringUtils;
use Pumukit\SchemaBundle\Services\UserService;
use Pumukit\SchemaBundle\Document\User;

class PumukitProvider implements AuthenticationProviderInterface
{
  private $userProvider;
  private $providerKey;
  private $userChecker;
  private $container;


  public function __construct(UserProviderInterface $userProvider, $providerKey, UserCheckerInterface $userChecker, ContainerInterface $container)
  {
    $this->userProvider = $userProvider;
    $this->providerKey = $providerKey;
    $this->userChecker = $userChecker;
    //NOTE: using container instead of tag service to avoid ServiceCircularReferenceException.
    $this->container = $container;
  }

  public function authenticate(TokenInterface $token)
  {
    if (!$this->supports($token)) {
      return;
    }

    if (!$user = $token->getUser()) {
      throw new BadCredentialsException('No pre-authenticated principal found in request.');
    }

    try {
        $user = $this->userProvider->loadUserByUsername($user);
    } catch(UsernameNotFoundException $notFound){
      $this->createUser($user);
      //TODO add use in ddbb.
        throw $notFound;
    } catch (\Exception $repositoryProblem) {
        $ex = new AuthenticationServiceException($repositoryProblem->getMessage(), 0, $repositoryProblem);
        $ex->setToken($token);
        throw $ex;
    }


    $this->userChecker->checkPreAuth($user);
    $this->userChecker->checkPostAuth($user);
    
    $authenticatedToken = new PreAuthenticatedToken($user, $token->getCredentials(), $this->providerKey, $user->getRoles());
    $authenticatedToken->setAttributes($token->getAttributes());

    return $authenticatedToken;
  }


  private function createUser($userName)
  {
    $userService = $this->container->get('pumukitschema.user');
    if($userService) {
      //TODO create createDefaultUser in UserService.
      //$this->userService->createDefaultUser($user);
      $user = new User();
      $user->setUsername($userName);
      $userService->create($user);
    } else {
      throw new AuthenticationServiceException("Not UserService to create a new user");
    }
  }


  public function supports(TokenInterface $token)
  {
    return $token instanceof PreAuthenticatedToken;
  }
}