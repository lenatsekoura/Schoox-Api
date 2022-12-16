<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Course;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Lib\Api\Validation;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api', name: 'api_')]
class CourseController extends ApiControllerBase
{
    #[Route('/courses', name: 'courses_index', methods:['GET'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        $courses = $doctrine
            ->getRepository(Course::class)
            ->findAll();

        return $this->json(data: $courses, context: [ObjectNormalizer::GROUPS => "list_courses"]);
    }

    #[Route('/courses', name: 'courses_new', methods:['POST'])]
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $validKeys = [
            'title' => 'required',
            'description' => 'optional',
            'status' => 'required',
            'is_premium' => 'required'
        ];

        $entityManager = $doctrine->getManager();

        $course = new Course();
        $data = json_decode($request->getContent(), true);

        if($finalResult = $this->keysValidationFailed($validKeys, $data)) {
            return new JsonResponse($finalResult, 400);
        }

        $data = $this->validateCommon($data);
        if($data instanceof JsonResponse) {
            return $data;
        }

        $course->setTitle($data['title']);
        if(isset($item['description'])) {
            $course->setDescription($data['description']);
        }
        $course->setStatus($data['status']);
        $course->setIsPremium($data['is_premium']);
        $course->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($course);
        $entityManager->flush();

        return $this->json('Created new course successfully with id ' . $course->getId(), 201);
    }

    #[Route('/courses/{id}', name: 'courses_show', methods:['GET'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $course = $doctrine->getRepository(Course::class)->find($id);

        if (!$course) {
            return $this->json('No course found for id ' . $id, 404);
        }

        return $this->json($course);
    }

    #[Route('/courses/{id}', name: 'course_edit', methods:['PUT'])]
    public function edit(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $validKeys = [
            'title' => 'required',
            'description' => 'optional',
            'status' => 'optional',
            'is_premium' => 'optional'
        ];

        $entityManager = $doctrine->getManager();
        $course = $entityManager->getRepository(Course::class)->find($id);

        if (!$course) {
            return $this->json('No course found for id' . $id, 404);
        }

        $data=json_decode($request->getContent(), true);

        if($finalResult = $this->keysValidationFailed($validKeys, $data)) {
            return new JsonResponse($finalResult, 400);
        }

        $data = $this->validateCommon($data);
        if($data instanceof JsonResponse) {
            return $data;
        }

        $course->setTitle($data['title']);
        if(isset($item['description'])) {
            $course->setDescription($data['description']);
        }
        if(isset($item['status'])) {
            $course->setStatus($data['status']);
        }
        if(isset($item['is_premium'])) {
            $course->setIsPremium($data['is_premium']);
        }

        $entityManager->flush();

        return $this->json($course, 202);
    }

    #[Route('/courses/{id}', name: 'course_delete', methods:['DELETE'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $course = $entityManager->getRepository(Course::class)->find($id);

        if (!$course) {
            return $this->json('No course found for id' . $id, 404);
        }

        $entityManager->remove($course);
        $entityManager->flush();

        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_NO_CONTENT);
        return $response;
    }

    private function validateCommon($data): Response|array
    {
        if(isset($data['title']) && !$this->validateTitle($data['title'])) {
            return new JsonResponse(
                ['title'=> 'Title is invalid: "'. ( $data['title'] ?? '<empty>') .'"' ],
                400
            );
        }

        if(isset($data['description']) && !$this->validateDescription($data['description'])) {
            return new JsonResponse(
                ['description'=> 'Description is invalid: "'. ( $data['description'] ?? '<empty>') .'"' ],
                400
            );
        }

        if(isset($data['status']) && !in_array($data['status'], ['Published', 'Pending'])) {
            return new JsonResponse(
                ['status'=> 'Status is invalid: "'. ( $data['status'] ?? '<empty>') .'" accepted values are Published or Pending' ],
                400
            );
        }

        if(isset($data['is_premium']) && !in_array($data['is_premium'], ['true', 'false'])) {
            return new JsonResponse(
                ['is_premium'=> 'Is_premium is invalid: "'. ( $data['is_premium'] ?? '<empty>') .'" accepted values are true or false' ],
                400
            );
        }

        return $data;
    }
}
