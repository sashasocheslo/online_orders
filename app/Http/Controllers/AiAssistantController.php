<?php

namespace App\Http\Controllers;

use App\Enums\AiProvider;
use App\Exceptions\AiProviderException;
use App\Exceptions\AiProviderNotConfiguredException;
use App\Http\Requests\AiRecommendationRequest;
use App\Models\Menu;
use App\Services\Contracts\AiAssistantServiceInterface;
use Illuminate\Http\JsonResponse;

class AiAssistantController extends Controller
{
    public function __construct(
        private readonly AiAssistantServiceInterface $aiAssistant,
    ) {}

    public function __invoke(Menu $menu, AiRecommendationRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $answer = $this->aiAssistant->recommend(
                $menu,
                AiProvider::from($data['provider']),
                $data['question'],
            );
        } catch (AiProviderNotConfiguredException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (AiProviderException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }

        return response()->json($answer->toArray());
    }
}
