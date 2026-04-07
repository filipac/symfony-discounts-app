<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/', name: 'product_index')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $search = $request->query->get('q');

        return $this->render('product/list.html.twig', [
            'products' => $productRepository->findActiveCatalog($search),
            'search' => $search,
        ]);
    }

    #[Route('/products/new', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($request->request->all('product')['price'] ?? null) {
                $product->setPrice((float) $request->request->all('product')['price']);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Product saved.');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'New product',
        ]);
    }

    #[Route('/products/{id}/edit', name: 'product_edit')]
    public function edit(Product $product, Request $request, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);
            $this->addFlash('success', 'Product updated.');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit product',
        ]);
    }
}
