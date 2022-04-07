<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/taches')]
class TaskController extends AbstractController
{
    #[Route('/', name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository, Request $request): Response
    {
        $tasks = $taskRepository->findAll();

        $doneTasks = [];
        $todoTasks = [];
        foreach ($tasks as $task) {
            if ($task->getIsDone()) {
                $doneTasks[] = $task;
            } else {
                $todoTasks[] = $task;
            }
        }

        if ($request->get('tab') && $request->get('tab') === 'done') {
            $tab = 'done';
        }

        return $this->render('task/index.html.twig', [
            'current_nav' => 'task',
            'tab' => $tab ?? 'todo',
            'doneTasks' => $doneTasks,
            'todoTasks' => $todoTasks,            
        ]);
    }

    #[Route('/nouvelle', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setAuthor($this->getUser());
            $taskRepository->add($task);
            $this->addFlash('success', 'La tâche a bien été créée !');
            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/new.html.twig', [
            'current_nav' => 'task',
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'current_nav' => 'task',
            'task' => $task,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_task_edit', methods: ['GET', 'POST'])]
    #[IsGranted(data: 'TASK_EDIT', subject: 'task', message: 'Vous n\'avez pas l\'autorisation d\'effectuer cette opération', statusCode: 403)]
    public function edit(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdatedBy($this->getUser());
            $task->setUpdatedAt(new \DateTimeImmutable());
            $taskRepository->add($task);
            $this->addFlash('success', 'La tâche a bien été mise à jour !');
            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/edit.html.twig', [
            'current_nav' => 'task',
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    #[IsGranted(data: 'TASK_DELETE', subject: 'task', message: 'Vous n\'avez pas l\'autorisation d\'effectuer cette opération', statusCode: 403)]
    public function delete(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $taskRepository->remove($task);
            $this->addFlash('warning', 'La tâche a bien été supprimée !');
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle', name: 'app_task_toggle', methods: ['POST'])]
    #[IsGranted(data: 'TASK_EDIT', subject: 'task', message: 'Vous n\'avez pas l\'autorisation d\'effectuer cette opération', statusCode: 403)]
    public function toggle(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        $isDone = true;
        if ($this->isCsrfTokenValid('toggle'.$task->getId(), $request->request->get('_token'))) {
            $isDone = $task->getIsDone();
            $task->setIsDone(!$isDone);
            $task->setUpdatedAt(new \DateTimeImmutable());
            $task->setUpdatedBy($this->getUser());
            $taskRepository->add($task);
            $this->addFlash('success', 'La tâche a été marquée : ' . ($isDone ? 'à faire' : 'faite') . ' !');
        }

        return $this->redirectToRoute('app_task_index', ['tab' => $isDone ? 'done' : 'todo'], Response::HTTP_SEE_OTHER);
    }
}
