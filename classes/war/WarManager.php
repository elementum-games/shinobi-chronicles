<?php

class WarManager {
    private System $system;
    private User $user;

    public function __construct(System $system, User $user)
    {
        $this->system = $system;
        $this->user = $user;
    }

    public function getOperationById(int $operation_id): Operation {
        $operation_result = $this->system->db->query("SELECT * FROM `operations` WHERE `operation_id` = {$operation_id} LIMIT 1");
        if ($this->system->db->last_num_rows == 0) {
            throw new RuntimeException("Operation not found");
        }
        $operation_result = $this->system->db->fetch($operation_result);
        $operation = new Operation($this->system, $this->user, $operation_result);
        return $operation;
    }

    public function processOperation(int $operation_id, ?int $status = null)
    {
        $operation = $this->getOperationById($operation_id);
        if (!empty($status)) {
            $operation->status = $status;
        }
        switch ($operation->status) {
            case Operation::OPERATION_ACTIVE:
                $operation->progressActiveOperation();
                break;
            case Operation::OPERATION_COMPLETE:
                $operation->handleCompletion();
                $operation->updateData();
                break;
            case Operation::OPERATION_FAILED:
                $operation->handleFailure();
                $operation->updateData();
                break;
            default:
                throw new RuntimeException("Invalid operation status!");
        }
    }
}