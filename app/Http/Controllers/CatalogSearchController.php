<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogSearchRequest;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\View\View;

class CatalogSearchController extends Controller
{
    private ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    public function __invoke(CatalogSearchRequest $request): View
    {
        return view(
            'catalog.search',
            $this->productService->searchCatalog($request->validated()),
        );
    }
}
