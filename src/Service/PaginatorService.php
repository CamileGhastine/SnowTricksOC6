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
        $this->page = $page;

        return [
            'comments' => $this->repo->findCommentsWithUser($id, self::MAX_RESULTS, ($page - 1) * self::MAX_RESULTS),
            'render' => $this->renderPagination(),
        ];
    }

    /**
     * render de HTML pagination links.
     *
     * @return string
     */
    private function renderPagination()
    {
        $numberPages = ceil(count($this->repo->findCommentsWithUser($this->id)) / self::MAX_RESULTS);

        $render = '';
        for ($i = 1; $i <= $numberPages; ++$i) {
            $render .= '<a href="/trick/'.$this->id.'/ajax-commentsPagination/'.$i.'"><span class="badge badge-pill badge-'.($i === $this->page ? 'page-activate' : 'page').' mx-2">'.$i.'</span></a> ';
        }

        return '<p class="mt-4 pagination justify-content-center">'.$render.'</p>';
    }
}
