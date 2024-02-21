<?php
class PremiumAPIPresenter {
    public static function getCosts(User $player): array {
        return PremiumManager::loadCosts(player: $player);
    }
}