<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    #[Route('/product/new', name: 'new_product')]
    public function createProduct(ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
        $entityManager = $doctrine->getManager();

        $product = new Products();
        $product->setName('Keyboard');
        $product->setPrice(1999);
        $product->setDescription('Ergonomic and stylish!');

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
        
    }

    #[Route('/product/{id}', name: 'get_product')]
    public function getProduct(ManagerRegistry $doctrine, int $id): Response
    {
        $product = $doctrine->getRepository(Products::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return $this->render('product/show.html.twig', ['product' => $product]);
        
    }

    /**
     * @Route("/product/other/{id}", name="product_show")
     */
    public function show(int $id, ProductsRepository $productRepository): Response
    {
        $product = $productRepository
            ->find($id);

        return new Response('Check out this great product: '.$product->getName());
    }

    /**
     * @Route("/product/other2/{id}", name="product_show2")
     */
    public function showProduct(Products $product): Response
    {
        return new Response('Check out this great product: '.$product->getName());
    }

    /**
     * @Route("/product/edit/{id}")
     */
    public function updateProduct(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $product->setName('New product name!');
        $entityManager->flush();

        return $this->redirectToRoute('product_show', [
            'id' => $product->getId()
        ]);
    }

    /**
     * @Route("/product/delete/{id}")
     */
    public function deleteProduct(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('product_show', [
            'id' => $product->getId()
        ]);
    }
}
