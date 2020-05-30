<?php

namespace App\Service;

use App\Repository\CommentRepository;

class PaginatorService
{
    const MAX_RESULTS = 5;
    private $repo;

    public function __construct(CommentRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function paginate(int $id, int $page)
    {
        return [
            'comments' => $this->repo->findCommentsWithUser($id, self::MAX_RESULTS, ($page - 1) * self::MAX_RESULTS),
            'render' => $this->renderPagination($id, $page),
        ];
    }

    /**
     * render de HTML pagination links.
     *
     * @return string
     */
    private function renderPagination(int $id, int $page)
    {
        $numberPages = ceil(count($this->repo->findCommentsWithUser($id)) / self::MAX_RESULTS);

        $render = '';
        for ($i = 1; $i <= $numberPages; ++$i) {
            $render .= '<a href="/trick/'.$id.'/ajax-commentsPagination/'.$i.'"><span class="badge badge-pill badge-'.($i === $page ? 'page-activate' : 'page').' mx-2">'.$i.'</span></a> ';
        }

        return '<p class="mt-4 pagination justify-content-center">'.$render.'</p>';
    }
}
