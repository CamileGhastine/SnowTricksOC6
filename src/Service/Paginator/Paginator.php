<?php

Namespace App\Service\Paginator;

use App\Repository\CommentRepository;

class Paginator
{
    const MAX_RESULTS = 3;
    private $repo;
    private $id;
    private $allComments;
    private $numberPages;
    private $page;

    public function __construct(CommentRepository $repo) {
        $this->repo = $repo;
    }

    public function paginate($id, int $page) {
        $this->id = $id;
        $this->allComments = $this->repo->findCommentsWithUser($id);
        $this->page = $page;
        $this->numberPages = ceil(count($this->allComments)/self::MAX_RESULTS);

        $paginatedComments = $this->selectComments();
        $render = $this->renderPagination();
        return ['comments' =>$paginatedComments, 'render' => $render ];
    }

    public function selectComments() {
        $paginatedComments = [];
        for ($i=($this->page-1)*self::MAX_RESULTS; $i<($this->page*self::MAX_RESULTS); $i++) {
            if (isset($this->allComments[$i])) $paginatedComments[] = $this->allComments[$i];
        }
        return $paginatedComments;
    }

    public function renderPagination() {
        $render="";
        for ($i=1; $i<=$this->numberPages; $i++) {
            $render .= '<a href="/trick/ajax/commentsPagination/'.$this->id.'/'.$i.'"><span class="badge badge-pill badge-'.($i === $this->page ? 'primary' : 'secondary').' mx-2">'.$i.'</span></a> ';
        }
        return '<p class="text-center mt-4 pagination">'.$render.'</p>';
    }
}