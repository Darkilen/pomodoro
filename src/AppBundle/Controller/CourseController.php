<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Course;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
//Serializer
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Course controller.
 *
 * @Route("/")
 */
class CourseController extends Controller
{
  /**
   * Lists all course entities.
   *
   * @Route("/", name="_index")
   * @Method("GET")
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $courses = $em->getRepository('AppBundle:Course')->findAll();

    return $this->render('course/index.html.twig', array(
      'courses' => $courses,
    ));
  }

  /**
   * Get a json.
   *
   * @Route("/json", name="_json")
   * @Method({"GET"})
   */
  public function jsonAction(Request $request)
  {
    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizers = array(new ObjectNormalizer());

    $serializer = new Serializer($normalizers, $encoders);
    $em = $this->getDoctrine()->getManager();

    $courses = $em->getRepository('AppBundle:Course')->findAll();
    $jsonContent = $serializer->serialize($courses, 'json');

    $response = new Response($jsonContent, 200, ['content-type', 'application/json']);
    $response->headers->set("Access-Control-Allow-Origin", "*");
    return $response;
  }

  /**
   * Creates a new course entity.
   *
   * @Route("/new", name="_new")
   * @Method({"GET", "POST"})
   */
  public function newAction(Request $request)
  {
    $title=$request->request->get("title");
    dump($title);
    $course = new Course();
    $course->setTitle($title);
    $course->setSummary("Default description");
    $course->setDateUse(new \DateTime());
    dump($request);

    if (isset($title) && $request->isMethod('POST')) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($course);
      $em->flush();
      $response = new Response("OK", 200);
      $response->headers->set("Access-Control-Allow-Origin", "*");
      return $response;
    }

    $response = new Response("Des soucis dans votre requète ? Allez voir Samuel et Mohamed !", 200);
    $response->headers->set("Access-Control-Allow-Origin", "*");
    return $response;
  }

  /**
   * Finds and displays a course entity.
   *
   * @Route("/{id}", name="_show")
   * @Method("GET")
   */
  public function showAction(Course $course)
  {
    $deleteForm = $this->createDeleteForm($course);

    return $this->render('course/show.html.twig', array(
      'course' => $course,
      'delete_form' => $deleteForm->createView(),
    ));
  }

  /**
   * Displays a form to edit an existing course entity.
   *
   * @Route("/{id}/edit", name="_edit")
   * @Method({"GET", "POST"})
   */
  public function editAction(Request $request, Course $course)
  {
    $deleteForm = $this->createDeleteForm($course);
    $editForm = $this->createForm('AppBundle\Form\CourseType', $course);
    $editForm->handleRequest($request);

    if ($editForm->isSubmitted() && $editForm->isValid()) {
      $this->getDoctrine()->getManager()->flush();

      return $this->redirectToRoute('_edit', array('id' => $course->getId()));
    }

    return $this->render('course/edit.html.twig', array(
      'course' => $course,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    ));
  }

  /**
   * Deletes a course entity.
   *
   * @Route("/{id}", name="_delete")
   * @Method("DELETE")
   */
  public function deleteAction(Request $request, Course $course)
  {
    $form = $this->createDeleteForm($course);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->remove($course);
      $em->flush();
    }

    return $this->redirectToRoute('_index');
  }

  /**
   * Creates a form to delete a course entity.
   *
   * @param Course $course The course entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createDeleteForm(Course $course)
  {
    return $this->createFormBuilder()
      ->setAction($this->generateUrl('_delete', array('id' => $course->getId())))
      ->setMethod('DELETE')
      ->getForm()
      ;
  }
}
