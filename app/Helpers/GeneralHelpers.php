<?php
// app/Helpers/GeneralHelpers.php

if (!function_exists('getFieldValueOrDefault')) {
    /**
     * Verifica a existência de dados em um campo específico de um record.
     *
     * @param mixed $record O registro a ser verificado.
     * @param string $field O campo a ser verificado no record.
     * @param string|null $default Valor padrão a ser retornado se o campo estiver vazio.
     * @return string|null Retorna o valor do campo ou o valor padrão.
     */
    function getFieldValueOrDefault(mixed $record, string $field, string $default = null): ?string
    {
        if (!$record) {
            return $default; // Se não houver registro, retorna o valor padrão
        }

        // Verifica se o campo existe no record e tem um valor
        return !empty($record->{$field}) ? $record->{$field} : $default;
    }
}
