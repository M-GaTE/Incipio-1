<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Controller;

use FrontBundle\Form\Type\MandateType;
use GuzzleHttp\Exception\RequestException as ClientRequestException;
use GuzzleHttp\Exception\TransferException as ClientTransferException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/mandates")
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MandateController extends BaseController
{
    /**
     * Lists all Mandate entities.
     *
     * @Route("/", name="mandates")
     *
     * @Method({"GET"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        $mandates = [];
        try {
            $mandates = $this->sendAndDecode(
                $this->createRequest('GET', 'api_mandates_cget', $request),
                true
            );
        } catch (ClientTransferException $exception) {
            $this->handleGuzzleException($exception);
        }

        return ['mandates' => $mandates];
    }

    /**
     * @Route("/new", name="mandates_new")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function newAction(Request $request)
    {
        $newForm = $this->createNewForm();
        $newForm->handleRequest($request);
        $formData = [];

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $formData = $newForm->getData();
            try {
                $mandate = $this->sendAndDecode(
                    $this->createRequest(
                        'POST',
                        'api_mandates_cpost',
                        $request,
                        [
                            'json' => $formData,
                        ]
                    )
                );

                // Mandate properly created, redirect to mandate show view
                $this->addFlash('success', 'Le mandat bien a été créé.');

                return $this->redirectToRoute('mandates_show', ['id' => $mandate['@id']]);
            } catch (ClientTransferException $exception) {
                $this->handleGuzzleException($exception);
            }
        }

        return ['new_form' => $this->createNewForm($formData)->createView()];
    }

    /**
     * Finds and displays a Mandate entity.
     *
     * @Route("/{id}", name="mandates_show")
     *
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     * @param int     $id
     *
     * @return array
     */
    public function showAction(Request $request, $id)
    {
        try {
            $mandate = $this->sendAndDecode(
                $this->createRequest(
                    'GET',
                    'api_mandates_get',
                    $request,
                    ['parameters' => ['id' => $id]]
                )
            );

            return [
                'delete_form' => $this->createDeleteForm($id)->createView(),
                'mandate' => $mandate,
            ];
        } catch (ClientRequestException $exception) {
            if (null !== $exception->getResponse()
                && Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()
            ) {
                throw $this->createNotFoundException('Unable to find Mandate entity.');
            }

            $this->handleGuzzleException($exception);
        } catch (ClientTransferException $exception) {
            $this->handleGuzzleException($exception);
        }

        return $this->redirectToRoute('mandates');
    }

    /**
     * Displays a form to edit an existing Mandate entity.
     *
     * @Route("/{id}/edit", name="mandates_edit")
     *
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     * @param int     $id
     *
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        try {
            $mandate = $this->sendAndDecode(
                $this->createRequest(
                    'GET',
                    'api_mandates_get',
                    $request,
                    ['parameters' => ['id' => $id]]
                )
            );

            return [
                'mandate' => $mandate,
                'edit_form' => $this->createEditForm($mandate)->createView(),
            ];
        } catch (ClientRequestException $exception) {
            if (null !== $exception->getResponse()
                && Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()
            ) {
                throw $this->createNotFoundException('Unable to find Mandate entity.');
            }

            $this->handleGuzzleException($exception);
        } catch (ClientTransferException $exception) {
            $this->handleGuzzleException($exception);
        }
    }

    /**
     * Edits an existing Mandate entity.
     *
     * @Route("/{id}", name="mandates_update")
     *
     * @Method("PUT")
     * @Template("FrontBundle:Mandate:edit.html.twig")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, $id)
    {
        $mandate = [];

        try {
            // Get the mandate to check if exist and to retrieve its data
            $mandate = $this->sendAndDecode(
                    $this->createRequest(
                    'GET',
                    'api_mandates_get',
                    $request,
                    ['parameters' => ['id' => $id]]
                )
            );

            // Handle update request
            $editForm = $this->createEditForm($mandate);
            $editForm->handleRequest($request);

            if ($editForm->isValid()) {
                $updateRequest = $this->createRequest('PUT',
                    'api_mandates_put',
                    $request,
                    [
                        'json' => $editForm->getData(),
                        'parameters' => ['id' => $id],
                    ]
                );

                $this->client->send($updateRequest);
                $this->addFlash('success', 'Le mandat a bien été mis à jour.');

                return $this->redirectToRoute('mandates_show', ['id' => $id]);
            }
        } catch (ClientRequestException $exception) {
            if (null !== $exception->getResponse()
                && Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()
            ) {
                throw $this->createNotFoundException('Unable to find Mandate entity.');
            }

            $this->handleGuzzleException($exception);
        } catch (ClientTransferException $exception) {
            $this->handleGuzzleException($exception);
        }

        return [
            'mandate' => $mandate,
            'edit_form' => $this->createEditForm($mandate)->createView(),
        ];
    }

    /**
     * Deletes a Mandate entity.
     *
     * @Route("/{id}", name="mandates_delete")
     *
     * @Method("DELETE")
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $deleteForm = $this->createDeleteForm($id);
        $deleteForm->handleRequest($request);

        if ($deleteForm->isValid()) {
            try {
                $this->client->send(
                    $this->createRequest(
                        'DELETE',
                        'api_mandates_delete',
                        $request,
                        [
                            'parameters' => ['id' => $id],
                        ]
                    )
                );
                $this->addFlash('success', 'Le mandat a bien été supprimé.');
            } catch (ClientRequestException $exception) {
                if (null !== $exception->getResponse()
                    && Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()
                ) {
                    throw $this->createNotFoundException('Unable to find Mandate entity.');
                }

                $this->handleGuzzleException($exception);
            } catch (ClientTransferException $exception) {
                $this->handleGuzzleException($exception);
            }
        } else {
            $this->addFlash('error', $deleteForm->getErrors());
        }

        return $this->redirectToRoute('mandates');
    }

    /**
     * Creates a form to create a Mandate entity.
     *
     * @param array|null $mandate The normalized mandate.
     *
     * @return \Symfony\Component\Form\Form
     */
    private function createNewForm(array $mandate = [])
    {
        $form = $this->createForm(new MandateType(),
            $mandate,
            [
                'action' => $this->generateUrl('mandates_new'),
                'method' => 'POST',
            ]
        );

        return $form;
    }

    /**
     * Creates a form to edit a Mandate entity.
     *
     * @param array $mandate The normalized mandate.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(array $mandate)
    {
        $form = $this->createForm(
            new MandateType(),
            $mandate,
            [
                'action' => $this->generateUrl('mandates_update', ['id' => $mandate['@id']]),
                'method' => 'PUT',
            ]
        );

        return $form;
    }

    /**
     * Creates a form to delete a Mandate entity by id.
     *
     * @param int $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('mandates_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
