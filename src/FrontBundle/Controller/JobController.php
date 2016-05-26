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

use FrontBundle\Form\Type\JobFilteringType;
use FrontBundle\Form\Type\JobType;
use GuzzleHttp\Exception\RequestException as ClientRequestException;
use GuzzleHttp\Exception\TransferException as ClientTransferException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/jobs")
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class JobController extends BaseController
{
    /**
     * Lists all Job entities.
     *
     * @Route("/", name="jobs")
     *
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        $filterForm = $this->createJobFilteringForm($request);
        $jobRequest = $this->createRequest('GET', 'api_jobs_cget', $request);

        // Check if a request has been made to filter the list of users
        if ('POST' === $request->getMethod()) {
            // Handle filter form
            $filterForm->handleRequest($request);

            if ($filterForm->isSubmitted() && $filterForm->isValid()) {
                $data = $filterForm->getData();
                $query = '';

                // Update user request to filter the list of users to match the requested type
                if (null !== $data['mandate_id']) {
                    $query .= sprintf('&filter[where][mandate]=%s', $data['mandate_id']);
                }

                $jobRequest->setQuery($query);
            }
        }

        // Retrieve users, since it's a paginated collection go through all available pages
        try {
            $jobs = $this->sendAndDecode($jobRequest, true);
        } catch (ClientTransferException $exception) {
            $this->handleGuzzleException($exception);
            $jobs = [];
        }

        return [
            'jobs' => $jobs,
            'filter_form' => $filterForm->createView(),
        ];
    }

    /**
     * @Route("/new", name="jobs_new")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function newAction(Request $request)
    {
        $newForm = $this->createNewForm($request);
        $newForm->handleRequest($request);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            try {
                $job = $this->sendAndDecode(
                    $this->createRequest(
                        'POST',
                        'api_jobs_cpost',
                        $request,
                        [
                            'json' => $newForm->getData(),
                        ]
                    )
                );

                // Job properly created, redirect to job show view
                $this->addFlash('success', 'Le poste bien a été créé.');

                return $this->redirectToRoute('jobs_show', ['id' => $job['@id']]);
            } catch (ClientTransferException $exception) {
                $this->handleGuzzleException($exception);
            }
        }

        return ['new_form' => $this->createNewForm($request, $newForm->getData())->createView()];
    }

    /**
     * Finds and displays a Job entity.
     *
     * @Route("/{id}", name="jobs_show")
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
            $job = $this->sendAndDecode(
                $this->createRequest(
                    'GET',
                    'api_jobs_get',
                    $request,
                    ['parameters' => ['id' => $id]]
                )
            );

            return [
                'delete_form' => $this->createDeleteForm($id)->createView(),
                'job' => $job,
            ];
        } catch (ClientRequestException $exception) {
            if (null !== $exception->getResponse()
                && Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()
            ) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $this->handleGuzzleException($exception);
        } catch (ClientTransferException $exception) {
            $this->handleGuzzleException($exception);
        }

        return $this->redirectToRoute('jobs');
    }

    /**
     * @Route("/{id}/edit", name="jobs_edit")
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
        $job = [];
        try {
            $job = $this->sendAndDecode(
                $this->createRequest(
                    'GET',
                    'api_jobs_get',
                    $request,
                    ['parameters' => ['id' => $id]]
                )
            );

            return [
                'job' => $job,
                'edit_form' => $this->createEditForm($job, $request)->createView(),
            ];
        } catch (ClientRequestException $exception) {
            if (null !== $exception->getResponse()
                && Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()
            ) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $this->handleGuzzleException($exception);
        } catch (ClientTransferException $exception) {
            $this->handleGuzzleException($exception);
        }

        return ['edit_form' => $this->createEditForm($job, $request)->createView()];
    }

    /**
     * Edits an existing Job entity.
     *
     * @Route("/{id}", name="jobs_update")
     *
     * @Method("PUT")
     * @Template("FrontBundle:Job:edit.html.twig")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, $id)
    {
        $job = [];

        try {
            // Get the job to check if exist and to retrieve its data
            $job = $this->sendAndDecode(
                $this->createRequest(
                    'GET',
                    'api_jobs_get',
                    $request,
                    ['parameters' => ['id' => $id]]
                )
            );

            // Handle update request
            $editForm = $this->createEditForm($job, $request);
            $editForm->handleRequest($request);

            if ($editForm->isValid()) {
                $this->client->send(
                    $this->createRequest(
                        'PUT',
                        'api_jobs_put',
                        $request,
                        [
                            'json' => $editForm->getData(),
                            'parameters' => ['id' => $id],
                        ]
                    )
                );
                $this->addFlash('success', 'Le poste a bien été mis à jour.');

                return $this->redirectToRoute('jobs_show', ['id' => $id]);
            }
        } catch (ClientRequestException $exception) {
            if (null !== $exception->getResponse()
                && Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()
            ) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $this->handleGuzzleException($exception);
        } catch (ClientTransferException $exception) {
            $this->handleGuzzleException($exception);
        }

        return [
            'job' => $job,
            'edit_form' => $this->createEditForm($job, $request)->createView(),
        ];
    }

    /**
     * Deletes a Job entity.
     *
     * @Route("/{id}", name="jobs_delete")
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
                        'api_jobs_delete',
                        $request,
                        [
                            'parameters' => ['id' => $id],
                        ]
                    )
                );
                $this->addFlash('success', 'Le poste a bien été supprimé.');
            } catch (ClientRequestException $exception) {
                if (null !== $exception->getResponse()
                    && Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()
                ) {
                    throw $this->createNotFoundException('Unable to find Job entity.');
                }

                $this->handleGuzzleException($exception);
            } catch (ClientTransferException $exception) {
                $this->handleGuzzleException($exception);
            }
        } else {
            $this->addFlash('error', $deleteForm->getErrors());
        }

        return $this->redirectToRoute('jobs');
    }

    /**
     * Creates a form to create a Job entity.
     *
     * @param array|null $job The normalized job.
     *
     * @return \Symfony\Component\Form\Form
     */
    private function createNewForm(Request $request, array $job = [])
    {
        $form = $this->createForm(new JobType($this->getMandates($request)),
            $job,
            [
                'action' => $this->generateUrl('jobs_new'),
                'method' => 'POST',
            ]
        );

        return $form;
    }

    /**
     * Creates a form to edit a Job entity.
     *
     * @param array   $job     The normalized job.
     * @param Request $request
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(array $job, Request $request)
    {
        $form = $this->createForm(
            new JobType($this->getMandates($request)),
            $job,
            [
                'action' => $this->generateUrl('jobs_update', ['id' => $job['@id']]),
                'method' => 'PUT',
            ]
        );

        return $form;
    }

    /**
     * Creates a form to delete a Job entity by id.
     *
     * @param int $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('jobs_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createJobFilteringForm(Request $request)
    {
        return $this->createForm(new JobFilteringType($this->getMandates($request)),
            [
                'action' => $this->generateUrl('jobs'),
                'method' => 'POST',
            ])
            ->add('submit', 'submit', ['label' => 'Filtrer'])
        ;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getMandates(Request $request)
    {
        $mandateFormValues = [];
        $mandates = $this->requestAndDecode(
            'GET',
            'api_mandates_cget',
            $request,
            ['query' => 'filter[order][startAt]=desc'],
            true
        );

        foreach ($mandates as $mandate) {
            $mandateFormValues[$mandate['@id']] = $mandate['name'];
        }

        return $mandateFormValues;
    }
}
