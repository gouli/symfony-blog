<?php

namespace MyBlogBundle\Controller;

use MyBlogBundle\Entity\Blog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BlogController extends Controller
{
 
    public function indexAction($id, Request $request)
    {
      $blog = new Blog();
      
      $repo = $this->getDoctrine()->getRepository('MyBlogBundle:Blog');
      $query = $repo->createQueryBuilder('b');
      if ( $id != 'all') {
        $query->where('b.id = :id');
        $query->setParameter('id', $id);
      }
      $getquery = $query->getQuery();
      
      $blogs = $getquery->getResult();
      
      if (empty($blogs)) {
        throw $this->createNotFoundException(
          'No Such Blog'
        );
      }
      
      $blogContent = array();
      $i = 0;
      
      foreach($blogs as $key => $value) {
        $blogContent[$i]['id'] = $value->getId();
        $blogContent[$i]['title'] = $value->getTitle();
        $blogContent[$i]['content'] = $value->getContent();
        $blogContent[$i]['author'] = $value->getAuthorId();
        $blogContent[$i]['created_date'] = $value->getCreatedDate();
        $blogContent[$i]['modified_date'] = $value->getModifiedDate();
        $i++;
      }
      //print '<pre>';print_r($blogContent);exit;
      return $this->render('MyBlogBundle:Myblog:blog.html.twig', array('blog' => $blogContent));
    }
    
    public function addAction(Request $request)
    {
      $blog = new Blog();
      
      $form = $this->createFormBuilder($blog)
        ->add('title', TextType::class)
        ->add('content', TextareaType::class, array('attr' => array('class' => 'tinymce')))
        ->add('author_id', IntegerType::class)
        ->add('save', SubmitType::class, array('label' => 'Save Blog'))
        ->getform();
      
      $form->handleRequest($request);
      
      if ($form->isSubmitted() && $form->isValid()) {
        //$current_date = new DateTime();
        $cd = date('Y-m-d H:i:s');
        $em = $this->getDoctrine()->getManager();
        $blog->setTitle($form->get('title')->getData());
        $blog->setContent($form->get('content')->getData());
        $blog->setAuthorId($form->get('author_id')->getData());
        $blog->setCreatedDate(new \DateTime('now'));
        $blog->setModifiedDate(new \DateTime('now'));
        
        $em->persist($blog);
        
        $em->flush();
        
        $this->addFlash(
          'notice',
          'Your changes were saved!'
        );        
      }
      
      return $this->render('MyBlogBundle:Myblog:form.html.twig', array(
          'form' => $form->createView(),
      ));
    }
}
