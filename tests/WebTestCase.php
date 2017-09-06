<?php

namespace Tests;

use Doctrine\ORM\EntityManager;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class WebTestCase extends BaseWebTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();

        $this->container = static::$kernel->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Asserts that a PHP value corresponds to the given JSON.
     *
     * @param mixed  $expected
     * @param string $actual
     */
    public function assertEqualsJson($expected, $actual)
    {
        $serializer = static::$kernel->getContainer()
            ->get('jms_serializer');

        $this->assertEquals($serializer->serialize($expected, 'json'), $actual);
    }

    /**
     * Asserts that the response status code is 400 (Bad Request).
     *
     * @param Response $response
     */
    public function assertIsBadRequest(Response $response)
    {
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * Asserts that the response status code is 201 (Created).
     *
     * @param Response $response
     * @param bool     $checkLocationHeader
     */
    public function assertIsCreated(Response $response, $checkLocationHeader = true)
    {
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        if ($checkLocationHeader) {
            $this->assertTrue($response->headers->has('Location'));
        }
    }

    /**
     * Asserts that the response status code is 204 (No Content).
     *
     * @param Response $response
     * @param bool     $checkContent
     */
    public function assertIsNoContent(Response $response, $checkContent = true)
    {
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        if ($checkContent) {
            $this->assertEmpty($response->getContent());
        }
    }

    /**
     * Asserts that the response status code is 404 (Not Found).
     *
     * @param Response $response
     */
    public function assertIsNotFound(Response $response)
    {
        $this->assertTrue($response->isNotFound());
    }

    /**
     * Asserts that the response status code is 200.
     *
     * @param Response $response
     */
    public function assertIsOk(Response $response)
    {
        $this->assertTrue($response->isOk());
    }

    /**
     * Asserts that the response's Content-Type is JSON.
     *
     * @param Response $response
     * @param bool     $checkContent
     */
    public function assertJsonResponse(Response $response, $checkContent = true)
    {
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        if ($checkContent) {
            $this->assertJson($response->getContent());
        }
    }

    /**
     * Creates a new OAuth Client.
     *
     * @return ClientInterface|mixed
     */
    public function createOAuthClient()
    {
        $clientManager = $this->container->get('fos_oauth_server.client_manager.default');

        $client = $clientManager->createClient();
        $client->setAllowedGrantTypes(['password', 'refresh_token']);

        $clientManager->updateClient($client);

        return $client;
    }

    /**
     * Deletes all rows of a table.
     *
     * @param string $name
     */
    public function emptyTable($name)
    {
        $this->em->getConnection()->executeUpdate('DELETE FROM ' . $name);
    }

    /**
     * Sends a POST request.
     *
     * @param Client $client
     * @param string $uri
     * @param string $content
     * @param array  $parameters
     * @param array  $files
     *
     * @return Response|null
     */
    public function post(Client $client, $uri, $content, array $parameters = [], array $files = [])
    {
        $client->request('POST', $uri, $parameters, $files, [
            'CONTENT_TYPE' => 'application/json'
        ], $content);

        return $client->getResponse();
    }

    /**
     * Sends a PUT request.
     *
     * @param Client $client
     * @param string $uri
     * @param string $content
     * @param array  $parameters
     * @param array  $files
     *
     * @return Response|null
     */
    public function put(Client $client, $uri, $content, array $parameters = [], array $files = [])
    {
        $client->request('PUT', $uri, $parameters, $files, [
            'CONTENT_TYPE' => 'application/json'
        ], $content);

        return $client->getResponse();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }
}
