<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiProductController extends AbstractController
{
    #[Route('/api/products/search', name: 'api_products_search', methods: ['GET'])]
    public function search(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $term = $request->query->get('q') ?: $request->query->get('term');

        if ($term === null) {
            return $this->json([
                'error' => 'Missing q parameter',
                'items' => [],
            ]);
        }

        if (mb_strlen(trim((string) $term)) < 2) {
            return $this->json([
                'data' => [],
                'meta' => ['count' => 0],
            ]);
        }

        try {
            $results = $productRepository->searchApiProducts((string) $term);
        } catch (\Throwable $exception) {
            return $this->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ]);
        }

        if ($request->query->getBoolean('compact')) {
            return $this->json($results);
        }

        return $this->json([
            'items' => $results,
            'count' => count($results),
            'query' => $term,
        ]);
    }
}
