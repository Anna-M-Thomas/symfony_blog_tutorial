<?php

namespace App\Controller;

use App\Entity\Post;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog_index")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository(Post::class)->findAll();

        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show", requirements={"id"="\d+"})
     */
    public function show($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);

        if(!$post){
            throw $this->createNotFoundException('The post does not exist');
        }

        return $this->render('blog/show.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @Route("/blog/new", name="blog_new")
     */
    public function new(Request $request)
    {
        // フォームの組立
        $post = new Post();
        $form = $this->createFormBuilder($post)
            ->add('title')
            ->add('content')
            ->getForm();

        // POST判定&バリデーション
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            // エンティティを永続化
            $em->persist($post);
            $em->flush();

            // 一覧へリダイレクト
            return $this->redirectToRoute('blog_index');
        }

        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/blog/{id}/edit", name="blog_edit", requirements={"id"="\d+"})
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id '.$id
            );
        }

        $form = $this->createFormBuilder($post)
            ->add('title')
            ->add('content')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // 更新します。
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }

        // 新規作成するときと同じテンプレートを利用
        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/blog/{id}/delete", name="blog_delete", requirements={"id"="\d+"})
     */
    function delete($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id '.$id
            );
        }
        // 削除
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('blog_index');
    }
   
}
