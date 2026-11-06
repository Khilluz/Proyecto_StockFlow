<?php

declare(strict_types=1);

namespace StockFlow\Services;

use DateTimeImmutable;
use StockFlow\Core\ApiException;
use StockFlow\DAO\GastoDAO;
use StockFlow\Models\Gasto;

final class GastoService
{
    public function __construct(private readonly GastoDAO $expenses)
    {
    }

    public function all(): array
    {
        return $this->expenses->all();
    }

    public function find(int $id): array
    {
        $expense = $this->expenses->find($id);
        if (!$expense) {
            throw new ApiException('No se encontró el gasto solicitado.', 404);
        }
        return $expense;
    }

    public function create(int $userId, array $input): array
    {
        $id = $this->expenses->create($userId, $this->validatedExpense($input));
        return $this->find($id);
    }

    public function update(int $id, array $input): array
    {
        $this->find($id);
        $this->expenses->update($id, $this->validatedExpense($input));
        return $this->find($id);
    }

    public function delete(int $id): void
    {
        if (!$this->expenses->delete($id)) {
            throw new ApiException('No se encontró el gasto solicitado.', 404);
        }
    }

    private function validatedExpense(array $input): Gasto
    {
        $category = $this->text($input, 'category', 80);
        $description = $this->text($input, 'description', 255);
        $amountValue = $input['amount'] ?? null;
        if ((!is_int($amountValue) && !is_float($amountValue) && !is_string($amountValue))
            || !is_numeric($amountValue)) {
            throw new ApiException('Ingresa un importe válido.', 400);
        }
        $amount = (float) $amountValue;
        if (!is_finite($amount) || $amount <= 0 || $amount > 9999999999.99
            || abs($amount - round($amount, 2)) > 0.0000001) {
            throw new ApiException('El importe debe ser mayor que cero y estar dentro del límite permitido.', 400);
        }

        $date = $input['expense_date'] ?? null;
        $parsedDate = is_string($date) ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
        $dateErrors = DateTimeImmutable::getLastErrors();
        if (!$parsedDate
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $parsedDate->format('Y-m-d') !== $date) {
            throw new ApiException('La fecha del gasto no es válida.', 400);
        }

        return new Gasto($category, $description, $amount, $date);
    }

    private function text(array $input, string $field, int $maxLength): string
    {
        $value = $input[$field] ?? null;
        if (!is_string($value)) {
            throw new ApiException('Completa todos los campos obligatorios del gasto.', 400);
        }
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > $maxLength) {
            throw new ApiException('Revisa la categoría y descripción del gasto.', 400);
        }
        return $value;
    }
}
