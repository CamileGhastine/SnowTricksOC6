<?php

namespace App\Service;

use App\Repository\CommentRepository;

class PaginatorService
{
    const MAX_RESULTS = 5;
    private $repo;
    private $id;
    private $allComments;
    private $numberPages;
    private $page;

    public function __construct(CommentRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function paginate($id, int $page)
    {
        $this->id = $id;
        $this->allComments = $this->repo->findCommentsWithUser($id);
        $this->page = $page;
        $this->numberPages = ceil(count($this->allComments) / self::MAX_RESULTS);

        return ['comments' => $this->selectComments(), 'render' => $this->renderPagination()];
    }

    /**
     * select paginate comments.
     *
     * @return array
     */
    private function selectComments()
    {
        $paginatedComments = [];
        for ($i = ($this->page - 1) * self::MAX_RESULTS; $i < ($this->page * self::MAX_RESULTS); ++$i) {
            if (isset($this->allComments[$i])) {
                $paginatedComments[] = $this->allComments[$i];
            }
        }

        return $paginatedComments;
    }

    /**
     * render de HTML pagination links.
     *
     * @return string
     */
    private function renderPagination()
    {
        $render = '';
        for ($i = 1; $i <= $this->numberPages; ++$i) {
            $render .= '<a href="/trick/'.$this->id.'/ajax-commentsPagination/'.$i.'"><span class="badge badge-pill badge-'.($i === $this->page ? 'page-activate' : 'page').' mx-2">'.$i.'</span></a> ';
        }

        return '<p class="mt-4 pagination justify-content-center">'.$render.'</p>';
    }
}
