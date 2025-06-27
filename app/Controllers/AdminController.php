<?php

namespace App\Controllers;

use App\Core\Contracts\ViewInterface;
use App\Core\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\AdminService;

class AdminController extends Controller
{
    public function __construct(
        protected ViewInterface $view,
        private readonly AdminService $service
    )
    {
    }

    #[Route('GET', '/admin/dashboard')]
    public function dashboard(Request $request): Response
    {
        $html = $this->view->render('admin/dashboard');
        return new Response($html);
    }

    // Usuários
    #[Route('GET', '/admin/users')]
    public function listUsers(Request $request): Response
    {
        $users = $this->service->listUsers();
        $html = $this->view->render('admin/users', ['users' => $users]);
        return new Response($html);
    }

    #[Route('POST', '/admin/users/create')]
    public function createUser(Request $request): Response
    {
        $data = $request->all();
        $this->service->createUser($data);
        return $this->redirect('/admin/users');
    }

    #[Route('POST', '/admin/users/update')]
    public function updateUser(Request $request): Response
    {
        $data = $request->all();
        $id = (int)($data['id'] ?? 0);
        if ($id) {
            $this->service->updateUser($id, $data);
        }
        return $this->redirect('/admin/users');
    }

    #[Route('POST', '/admin/users/delete')]
    public function deleteUser(Request $request): Response
    {
        $id = (int)$request->input('id');
        if ($id) {
            $this->service->deleteUser($id);
        }
        return $this->redirect('/admin/users');
    }

    // Setores
    #[Route('GET', '/admin/sectors')]
    public function listSectors(Request $request): Response
    {
        $sectors = $this->service->listSectors();
        $html = $this->view->render('admin/sectors', ['sectors' => $sectors]);
        return new Response($html);
    }

    #[Route('POST', '/admin/sectors/create')]
    public function createSector(Request $request): Response
    {
        $data = $request->all();
        $this->service->createSector($data);
        return $this->redirect('/admin/sectors');
    }

    #[Route('POST', '/admin/sectors/update')]
    public function updateSector(Request $request): Response
    {
        $data = $request->all();
        $id = (int)($data['id'] ?? 0);
        if ($id) {
            $this->service->updateSector($id, $data);
        }
        return $this->redirect('/admin/sectors');
    }

    #[Route('POST', '/admin/sectors/delete')]
    public function deleteSector(Request $request): Response
    {
        $id = (int)$request->input('id');
        if ($id) {
            $this->service->deleteSector($id);
        }
        return $this->redirect('/admin/sectors');
    }

    // Guichês
    #[Route('GET', '/admin/counters')]
    public function listCounters(Request $request): Response
    {
        $counters = $this->service->listCounters();
        $html = $this->view->render('admin/counters', ['counters' => $counters]);
        return new Response($html);
    }

    #[Route('POST', '/admin/counters/create')]
    public function createCounter(Request $request): Response
    {
        $data = $request->all();
        $this->service->createCounter($data);
        return $this->redirect('/admin/counters');
    }

    #[Route('POST', '/admin/counters/update')]
    public function updateCounter(Request $request): Response
    {
        $data = $request->all();
        $id = (int)($data['id'] ?? 0);
        if ($id) {
            $this->service->updateCounter($id, $data);
        }
        return $this->redirect('/admin/counters');
    }

    #[Route('POST', '/admin/counters/delete')]
    public function deleteCounter(Request $request): Response
    {
        $id = (int)$request->input('id');
        if ($id) {
            $this->service->deleteCounter($id);
        }
        return $this->redirect('/admin/counters');
    }

    // Tipos de senha
    #[Route('GET', '/admin/ticket-types')]
    public function listTicketTypes(Request $request): Response
    {
        $ticketTypes = $this->service->listTicketTypes();
        $html = $this->view->render('admin/ticket_types', ['ticketTypes' => $ticketTypes]);
        return new Response($html);
    }

    #[Route('POST', '/admin/ticket-types/create')]
    public function createTicketType(Request $request): Response
    {
        $data = $request->all();
        $this->service->createTicketType($data);
        return $this->redirect('/admin/ticket-types');
    }

    #[Route('POST', '/admin/ticket-types/update')]
    public function updateTicketType(Request $request): Response
    {
        $data = $request->all();
        $id = (int)($data['id'] ?? 0);
        if ($id) {
            $this->service->updateTicketType($id, $data);
        }
        return $this->redirect('/admin/ticket-types');
    }

    #[Route('POST', '/admin/ticket-types/delete')]
    public function deleteTicketType(Request $request): Response
    {
        $id = (int)$request->input('id');
        if ($id) {
            $this->service->deleteTicketType($id);
        }
        return $this->redirect('/admin/ticket-types');
    }

    private function redirect(string $url): Response
    {
        return new Response('', 302, ['Location' => $url]);
    }
}