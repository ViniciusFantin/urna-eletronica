<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

class AuditoriaService
{
    public function __construct(
        private Connection $db
    ) {}

    public function registrar(
        string $evento,
        array $dados = []
    ): void {
        // Deve ser chamado dentro da transação da votação.
        // O VotoController já bloqueia a linha da eleição.

        $anterior = $this->db->fetchOne(
            'SELECT current_hash
             FROM audit_log
             ORDER BY id DESC
             LIMIT 1'
        );

        $anterior = $anterior === false ? null : $anterior;

        $data = json_encode(
            $dados,
            JSON_THROW_ON_ERROR
        );

        $criadoEm = gmdate('Y-m-d H:i:s');

        $hash = hash(
            'sha256',
            ($anterior ?? '') . '|' .
            $evento . '|' .
            $data . '|' .
            $criadoEm
        );

        $this->db->insert('audit_log', [
            'event' => $evento,
            'data' => $data,
            'previous_hash' => $anterior,
            'current_hash' => $hash,
            'signature' => null,
            'created_at' => $criadoEm
        ]);
    }

    public function consultar(): array
    {
        $registros = $this->db->fetchAllAssociative(
            'SELECT *
             FROM audit_log
             ORDER BY id ASC'
        );

        $anterior = null;
        $resultado = [];

        foreach ($registros as $registro) {
            $esperado = hash(
                'sha256',
                ($anterior ?? '') . '|' .
                $registro['event'] . '|' .
                $registro['data'] . '|' .
                $registro['created_at']
            );

            $integro =
                $registro['previous_hash'] === $anterior &&
                hash_equals(
                    $esperado,
                    $registro['current_hash']
                );

            $registro['integro'] = $integro;
            $resultado[] = $registro;

            // Usa o hash armazenado para verificar
            // corretamente a próxima ligação da cadeia.
            $anterior = $registro['current_hash'];
        }

        return $resultado;
    }
}