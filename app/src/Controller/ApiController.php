<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function array_chunk;
use function count;
use function file_get_contents;
use function is_array;
use function is_string;
use function json_decode;

/**
 * Class ApiController
 * @package App\Controller
 * @author  Kristo Leas <kristo.leas@gmail.com>
 */
final class ApiController {
	public function __construct(private EntityManagerInterface $em) { }
	
	public function store(Request $request): JsonResponse {
		// This is to ensure a clean database for testing purposes, ignore when code is final
//		foreach ($this->em->getRepository(Organization::class)->findAll() as $item) {
//			$this->em->remove($item);
//			$this->em->flush();
//		}
		// For reasons quite not clear, when sending pure JSON requests, PHP does not fill the $_POST array, instead need to get
		// data directly from the input.
		if ($request->request->count() === 0) {
			$organizations = (array) json_decode(file_get_contents('php://input', true), true);
		} else {
			$organizations = $request->request;
		}
		$this->process($organizations);
		$response = new JsonResponse();
		$response->prepare($request)->send();
		return $response;
	}
	
	public function retrieve(Request $request): JsonResponse {
		// For reasons quite not clear, when sending pure JSON requests, PHP does not fill the $_POST array, instead need to get
		// data directly from the input.
		if ($request->request->count() === 0) {
			$nameAndPage = (array) json_decode(file_get_contents('php://input', true), true);
		} else {
			$nameAndPage = $request->request;
		}
		// If no name is sent for us to find, why bother going forward. Immediately slap the user with bad request
		if (! $nameAndPage['name']) {
			$response = new JsonResponse([], Response::HTTP_BAD_REQUEST);
			$response->prepare($request)->send();
			return $response;
		}
		// Retrieve all items at once.
		// Now granted, IF we would go into really large data sets, this is a horrible solution.
		// However, requirement was not to use a framework and in any larger app, a framework of some sort would most likely be used.
		// Unless done as a functional app, but I only see the world as objects.
		$items = $this->em->getRepository(Organization::class)->findOneBy(['name' => $nameAndPage['name']])->__toArray();
		// Again, a horrible solution for pagination.
		// This is where I would instead use custom repository methods to retrieve a limited data set. Perhaps even use redis to speed things up further.
		if (count($items) > 100) {
			$paged    = array_chunk($items, 100, true);
			$response = new JsonResponse($paged[$nameAndPage['page'] ?? 1]);
		} else {
			$response = new JsonResponse($items);
		}
		$response->prepare($request)->send();
		return $response;
	}
	
	private function process(array $organizations, ?Organization $parent = null): void {
		foreach ($organizations as $organization) {
			if (is_string($organization)) {
				// If we have the entity already in the database, retrieve it and add either daughters or parents
				$existing = $this->em->getRepository(Organization::class)->findOneBy(['name' => $organization]);
				if ($existing === null) {
					$newOrg = new Organization($organization);
				} else {
					$newOrg = $existing;
				}
				// Ensure the relationships exist properly on both sides
				if ($parent !== null) {
					$newOrg->addParent($parent);
					$parent->addDaughter($newOrg);
				}
				// Persist entity and flush to the database. For efficiency, flushing can be moved outside this method and done per bulk operation
				$this->em->persist($newOrg);
				$this->em->flush();
			}
			// Any case of an array, recursively go through and find all pesky bananas
			if (is_array($organization)) {
				$this->process($organization, $newOrg ?? $parent);
			}
		}
	}
}