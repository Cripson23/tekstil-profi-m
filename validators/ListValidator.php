<?php

namespace validators;

class ListValidator
{
	const MIN_PAGE = 1;
	const MAX_PAGE = 100;

	/**
	 * @param array $listParams
	 * @param array $settingFields
	 * @return array
	 */
	public static function validate(array $listParams, array $settingFields): array
	{
		return array_merge(
			self::validateFilter($listParams['filters'] ?? [], $settingFields['FILTER'] ?? []),
			self::validateSort($listParams['sort'] ?? [], $settingFields['SORT'] ?? []),
			self::validatePagination($listParams['pagination'] ?? [], $settingFields['PAGINATION'] ?? [])
		);
	}

	/**
	 * @param array $filters
	 * @param array $filterFields
	 * @return array
	 */
	private static function validateFilter(array $filters, array $filterFields): array
	{
		$errors = [];

		foreach ($filters as $field => $value) {
			if (!in_array($field, $filterFields['ALL'])) {
				$errors['filters'][$field] = 'Некорректное поле для фильтрации';
			} else if ($value === null || $value === '') {
				$errors['filters'][$field] = 'Значение должно быть заполнено';
			} else if (is_array($value)) {
				if (!in_array($field, $filterFields['INT'])) {
					$errors['filters'][$field] = 'Некорректное поле для фильтрации по диапазону';
				}
				else if (!isset($value['from']) || !isset($value['to']) ||
					filter_var($value['from'], FILTER_VALIDATE_INT) === false ||
					filter_var($value['to'], FILTER_VALIDATE_INT) === false ||
					$value['from'] > $value['to']
				) {
					$errors['filters'][$field] = 'Некорректное значение для фильтрации по диапазону';
				}
			} else {
				if (in_array($field, $filterFields['INT']) && filter_var($value, FILTER_VALIDATE_INT) === false) {
					$errors['filters'][$field] = 'Значение должно быть целым числом';
				// Используем замыкания для дополнительных проверок
				} else if (isset($filterFields['ADDITIONAL'][$field]) && !$filterFields['ADDITIONAL'][$field]($value)) {
					$errors['filters'][$field] = 'Некорректное значение';
				}
			}
		}

		return $errors;
	}

	/**
	 * @param array $sort
	 * @param array $sortFields
	 * @return array
	 */
	private static function validateSort(array $sort, array $sortFields): array
	{
		$errors = [];

		if (isset($sort['field']) && !in_array($sort['field'], $sortFields)) {
			$errors['sort']['field'] = 'Некорректное поле для сортировки';
		}

		if (isset($sort['direction']) && !in_array($sort['direction'], ['ASC', 'DESC'])) {
			$errors['sort']['direction'] = 'Некорректное значение для направления сортировки';
		}

		return $errors;
	}

    /**
     * @param array $pagination
     * @param $paginationSetting
     * @return array
     */
	private static function validatePagination(array $pagination, $paginationSetting): array
	{
		$errors = [];

		if (isset($pagination['page']) && (
			filter_var(
				$pagination['page'], FILTER_VALIDATE_INT) === false ||
				$pagination['page'] > self::MAX_PAGE ||
				$pagination['page'] < self::MIN_PAGE
		)) {
			$errors['pagination']['page'] = 'Некорректное значение для номера страницы';
		}

		if (isset($pagination['per_page'])) {
            if ((filter_var($pagination['per_page'], FILTER_VALIDATE_INT) === false)
                || (isset($paginationSetting['RANGE']) && !in_array($pagination['per_page'], $paginationSetting['RANGE']))
                || ($pagination['per_page'] > self::MAX_PAGE || $pagination['per_page'] < self::MIN_PAGE
            )) {
                $errors['pagination']['per_page'] = 'Некорректное значение для количества на одну страницу';
            }
        }

		return $errors;
	}
}