<?php

namespace App\Services;

use App\Models\Clinic;

class SubscriptionService
{
    /**
     * The features available for each subscription plan.
     *
     * @var array
     */
    protected $planFeatures = [
        'free' => [
            'staff_limit' => 2,
            'pets_limit' => 20,
            'clients_limit' => 20,
            'appointments_limit' => 30,
            'inventory_limit' => 50,
            'inventory_management' => false,
            'reports' => false,
            'analytics' => false,
            'theme_customization' => 'none',
        ],
        'basic' => [
            'staff_limit' => 5,
            'pets_limit' => 100,
            'clients_limit' => 100,
            'appointments_limit' => 200,
            'inventory_limit' => 100,
            'inventory_management' => true,
            'reports' => false,
            'analytics' => false,
            'theme_customization' => 'basic',
        ],
        'premium' => [
            'staff_limit' => -1, // Unlimited
            'pets_limit' => -1, // Unlimited
            'clients_limit' => -1, // Unlimited
            'appointments_limit' => -1, // Unlimited
            'inventory_limit' => -1, // Unlimited
            'inventory_management' => true,
            'reports' => true,
            'analytics' => true,
            'theme_customization' => 'full',
        ],
        'standard' => [
            'staff_limit' => -1, // Unlimited
            'pets_limit' => -1, // Unlimited
            'clients_limit' => -1, // Unlimited
            'appointments_limit' => -1, // Unlimited
            'inventory_limit' => -1, // Unlimited
            'inventory_management' => true,
            'reports' => true,
            'analytics' => true,
            'theme_customization' => 'full',
        ],
        'business' => [
            'staff_limit' => -1, // Unlimited
            'pets_limit' => -1, // Unlimited
            'clients_limit' => -1, // Unlimited
            'appointments_limit' => -1, // Unlimited
            'inventory_limit' => -1, // Unlimited
            'inventory_management' => true,
            'reports' => true,
            'analytics' => true,
            'theme_customization' => 'advanced',
        ]
    ];

    /**
     * Get the features for a specific plan.
     *
     * @param string $plan
     * @return array
     */
    public function getPlanFeatures($plan)
    {
        return $this->planFeatures[$plan] ?? $this->planFeatures['free'];
    }

    /**
     * Check if a clinic has access to a specific feature.
     *
     * @param Clinic $clinic
     * @param string $feature
     * @return bool
     */
    public function hasFeatureAccess(Clinic $clinic, $feature)
    {
        if (!$clinic->is_subscription_active) {
            return false;
        }

        $plan = $clinic->subscription_plan ?? 'free';
        $features = $this->getPlanFeatures($plan);

        return isset($features[$feature]) && $features[$feature] === true;
    }

    /**
     * Get theme customization level for a clinic.
     *
     * @param Clinic $clinic
     * @return string 'none', 'basic', or 'full'
     */
    public function getThemeCustomizationLevel(Clinic $clinic)
    {
        if (!$clinic->is_subscription_active) {
            return 'none';
        }

        $plan = $clinic->subscription_plan ?? 'free';
        $features = $this->getPlanFeatures($plan);

        return $features['theme_customization'] ?? 'none';
    }

    /**
     * Check if a clinic has reached a limit for a specific feature.
     *
     * @param Clinic $clinic
     * @param string $limitType
     * @param int $currentCount
     * @return bool
     */
    public function hasReachedLimit(Clinic $clinic, $limitType, $currentCount)
    {
        if (!$clinic->is_subscription_active) {
            return true;
        }

        $plan = $clinic->subscription_plan ?? 'free';
        $features = $this->getPlanFeatures($plan);

        $limit = $features[$limitType] ?? 0;
        
        // -1 means unlimited
        if ($limit === -1) {
            return false;
        }

        return $currentCount >= $limit;
    }

    /**
     * Get the limit for a specific feature.
     *
     * @param Clinic $clinic
     * @param string $limitType
     * @return int|string
     */
    public function getLimitForFeature(Clinic $clinic, $limitType)
    {
        $plan = $clinic->subscription_plan ?? 'free';
        $features = $this->getPlanFeatures($plan);
        
        $limit = $features[$limitType] ?? 0;
        
        // Return "Unlimited" for -1 values
        if ($limit === -1) {
            return "Unlimited";
        }
        
        return $limit;
    }

    /**
     * Get the available features description for a specific plan.
     *
     * @param string $plan
     * @return array
     */
    public function getPlanDescription($plan)
    {
        $features = $this->getPlanFeatures($plan);
        $description = [];

        foreach ($features as $feature => $value) {
            if (strpos($feature, 'limit') !== false) {
                $featureName = str_replace('_limit', '', $feature);
                $featureName = ucfirst(str_replace('_', ' ', $featureName));
                
                if ($value === -1) {
                    $description[] = "Unlimited {$featureName}";
                } else {
                    $description[] = "{$featureName} Limit: {$value}";
                }
            } else if ($feature === 'theme_customization') {
                switch ($value) {
                    case 'none':
                        $description[] = "Default Theme Only";
                        break;
                    case 'basic':
                        $description[] = "Basic Theme Customization (Light/Dark)";
                        break;
                    case 'full':
                        $description[] = "Full Theme Customization";
                        break;
                    case 'advanced':
                        $description[] = "Advanced Color Palette Customization";
                        break;
                }
            } else {
                if ($value === true) {
                    $description[] = ucfirst(str_replace('_', ' ', $feature));
                }
            }
        }

        return $description;
    }
} 