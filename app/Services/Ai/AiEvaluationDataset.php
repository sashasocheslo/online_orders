<?php

namespace App\Services\Ai;

use InvalidArgumentException;
use JsonException;

final class AiEvaluationDataset
{
    /**
     * @return array{
     *     version: string,
     *     restaurant: string,
     *     products: list<array{id: int, name: string, description: string|null, price: int|float, size: string|null, category: string|null}>,
     *     scenarios: list<array{id: string, question: string, allowed_product_ids: list<int>, expected_product_ids: list<int>}>
     * }
     */
    public function load(?string $path = null): array
    {
        $path ??= resource_path('ai/evaluation-dataset.json');
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new InvalidArgumentException("Не вдалося прочитати dataset: {$path}");
        }

        try {
            $dataset = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Dataset містить некоректний JSON.', previous: $exception);
        }

        if (! is_array($dataset)) {
            throw new InvalidArgumentException('Dataset повинен бути JSON-об’єктом.');
        }

        $this->validate($dataset);

        return $dataset;
    }

    /** @param array<string, mixed> $dataset */
    private function validate(array $dataset): void
    {
        if (! is_string($dataset['version'] ?? null) || trim($dataset['version']) === '') {
            throw new InvalidArgumentException('Dataset повинен містити непорожню версію.');
        }

        if (! is_string($dataset['restaurant'] ?? null) || trim($dataset['restaurant']) === '') {
            throw new InvalidArgumentException('Dataset повинен містити назву ресторану.');
        }

        if (! is_array($dataset['products'] ?? null) || $dataset['products'] === []) {
            throw new InvalidArgumentException('Dataset повинен містити товари.');
        }

        if (! is_array($dataset['scenarios'] ?? null) || $dataset['scenarios'] === []) {
            throw new InvalidArgumentException('Dataset повинен містити сценарії.');
        }

        $productIds = [];

        foreach ($dataset['products'] as $product) {
            if (! is_array($product)
                || ! is_int($product['id'] ?? null)
                || ! is_string($product['name'] ?? null)
                || ! is_numeric($product['price'] ?? null)) {
                throw new InvalidArgumentException('Кожен товар повинен мати числовий ID, назву та ціну.');
            }

            $productIds[] = $product['id'];
        }

        if (count($productIds) !== count(array_unique($productIds))) {
            throw new InvalidArgumentException('ID товарів у dataset мають бути унікальними.');
        }

        $scenarioIds = [];

        foreach ($dataset['scenarios'] as $scenario) {
            if (! is_array($scenario)
                || ! is_string($scenario['id'] ?? null)
                || ! is_string($scenario['question'] ?? null)
                || ! $this->isIntegerList($scenario['allowed_product_ids'] ?? null)
                || ! $this->isIntegerList($scenario['expected_product_ids'] ?? null)) {
                throw new InvalidArgumentException('Кожен сценарій повинен мати ID, запит і списки допустимих та очікуваних товарів.');
            }

            $scenarioIds[] = $scenario['id'];
            $annotatedIds = array_merge(
                $scenario['allowed_product_ids'],
                $scenario['expected_product_ids'],
            );

            if (array_diff($annotatedIds, $productIds) !== []) {
                throw new InvalidArgumentException("Сценарій {$scenario['id']} посилається на невідомий товар.");
            }

            if (array_diff($scenario['expected_product_ids'], $scenario['allowed_product_ids']) !== []) {
                throw new InvalidArgumentException("Очікувані товари сценарію {$scenario['id']} мають бути допустимими.");
            }
        }

        if (count($scenarioIds) !== count(array_unique($scenarioIds))) {
            throw new InvalidArgumentException('ID сценаріїв у dataset мають бути унікальними.');
        }
    }

    private function isIntegerList(mixed $value): bool
    {
        return is_array($value)
            && array_is_list($value)
            && collect($value)->every(fn (mixed $id): bool => is_int($id));
    }
}
