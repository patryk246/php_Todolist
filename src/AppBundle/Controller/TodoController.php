<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Item;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class TodoController extends Controller
{
    /**
     * @Route("/", name="todo_list")
     */
    public function listAction()
    {
        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' ) )
      {
        $iduser = $this->container->get('security.token_storage')->getToken()->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
          'SELECT i
          FROM AppBundle:Item i
          WHERE i.userId = :iduser'
        )->setParameter('iduser', $iduser);

        $items = $query->getResult();

        return $this->render('todo/index.html.twig', 
            array('items' => $items
            ));
        }
      return $this->render('todo/index.html.twig');
    }

    /**
     * @Route("/todo/create", name="todo_create")
     */
    public function createAction(Request $request)
    {
        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' ) )
      {
         $iduser = $this->container->get('security.token_storage')->getToken()->getUser()->getId();

        $item = new Item;
        $form = $this->createFormBuilder($item)
        ->add('name', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'magrin-bottom:15px')))
        ->add('category', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'magrin-bottom:15px')))
        ->add('number', IntegerType::class, array('attr' => array('class' => 'form-control', 'style' => 'magrin-bottom:15px')))
        ->add('priority', ChoiceType::class, array('choices' => array('Low' =>'Low', 'Normal' =>'Normal', 'High' =>'High'), 'attr' => array('class' => 'form-control', 'style' => 'magrin-bottom:15px')))
        ->add('due_date', DateTimeType::class, array('attr' => array('class' => 'formcontrol', 'style' => 'magrin-bottom:15px')))
        ->add('save', SubmitType::class, array('label' => 'Create Item', 'attr' => array('class' => 'btn btn-primary', 'style' => 'magrin-bottom:15px')))
        ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // Get Data
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $number = $form['number']->getData();
            $priority = $form['priority']->getData();
            $due_date = $form['due_date']->getData();

            $now = new\DateTime('now');

            $item->setName($name);
            $item->setCategory($category);
            $item->setNumber($number);
            $item->setPriority($priority);
            $item->setDueDate($due_date);
            $item->setCreateDate($now);
            $item->setUserId($iduser);

            $em = $this->getDoctrine()->getManager();
            $em->persist($item);
            $em->flush();

            $this->addFlash(
                'notice',
                'Item Added'
                );

            return $this->redirectToRoute('todo_list');
        }

        return $this->render('todo/create.html.twig', array(
            'form' => $form->createView()
            ));
        }
      return $this->render('todo/index.html.twig');
    }

    /**
     * @Route("/todo/edit/{id}", name="todo_edit")
     */
    public function editAction($id, Request $request)
    {
        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' ) )
      {
        $item = $this->getDoctrine()
        ->getRepository('AppBundle:Item')
        ->find($id);

        $form = $this->createFormBuilder( $item );

        $form = $this->createFormBuilder($item)
        ->add('name', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'magrin-bottom:15px')))
        ->add('category', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'magrin-bottom:15px')))
        ->add('number', IntegerType::class, array('attr' => array('class' => 'form-control', 'style' => 'magrin-bottom:15px')))
        ->add('priority', ChoiceType::class, array('choices' => array('Low' =>'Low', 'Normal' =>'Normal', 'High' =>'High'), 'attr' => array('class' => 'form-control', 'style' => 'magrin-bottom:15px')))
        ->add('due_date', DateTimeType::class, array('attr' => array('class' => 'formcontrol', 'style' => 'magrin-bottom:15px')))
        ->add('save', SubmitType::class, array('label' => 'Edit Item', 'attr' => array('class' => 'btn btn-primary', 'style' => 'magrin-bottom:15px')))
        ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $name = $form['name']->getData();
            $category = $form['category']->getData();
            $number = $form['number']->getData();
            $priority = $form['priority']->getData();
            $due_date = $form['due_date']->getData();

            $now = new\DateTime('now');

            $em = $this->getDoctrine()->getManager();
            $item = $em->getRepository('AppBundle:Item')->find($id);

            $item->setName($name);
            $item->setCategory($category);
            $item->setNumber($number);
            $item->setPriority($priority);
            $item->setDueDate($due_date);
            $item->setCreateDate($now);

            $em->flush();

            $this->addFlash(
                'notice',
                'Item Updated'
                );

            return $this->redirectToRoute('todo_list');
        }

        return $this->render('todo/edit.html.twig', 
            array('item' => $item,
                'form' => $form->createView()
            ));
        }
      return $this->render('todo/index.html.twig');
    }

    /**
     * @Route("/todo/details/{id}", name="todo_details")
     */
    public function detailstAction($id)
    {
        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' ) )
      {
        $item = $this->getDoctrine()
        ->getRepository('AppBundle:Item')
        ->find($id);

        return $this->render('todo/details.html.twig', 
            array('item' => $item
            ));
        }
      return $this->render('todo/index.html.twig');
                }

/**
     * @Route("/todo/delete/{id}", name="todo_delete")
     */
    public function deleteAction($id)
    {
        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' ) )
      {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('AppBundle:Item')->find($id);

        $em->remove($item);
        $em->flush();
        $this->addFlash(
                'notice',
                'Item Removed'
                );

        return $this->redirectToRoute('todo_list');
        }
      return $this->render('todo/index.html.twig');
    }
}