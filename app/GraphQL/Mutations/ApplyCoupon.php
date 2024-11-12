<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\CustomException;
use App\Models\Coupon;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ApplyCoupon
{
    public function __invoke($_, array $args, GraphQLContext $context)
    {
        $couponCode = $args['code'];
        $serviceIds = $args['services'] ?? [];
        $user = $context->user;
        $coupon = Coupon::where('code', $couponCode)->first();

        if ($coupon->status !== 'active') {
            throw new CustomException('Kupón expiroval', ['expirated' => true]);
        }

        if (!$coupon) {
            throw new \Exception('Kupón nebyl nenalezen', 404, ['aa' => 'aa']);
        }

        $couponRules = json_decode($coupon->rules);

        $agentRule = $couponRules->agent_ids === "" ? null : $couponRules->agent_ids;
        $customerRule = $couponRules->customer_ids === "" ? null : explode(",", $couponRules->customer_ids);
        $serviceRule = $couponRules->service_ids === "" ? null : explode(",", $couponRules->service_ids);

        if ($serviceRule) {
            $couponServices = array_intersect($serviceRule, $serviceIds);
            if (empty($couponServices)) {
                throw new CustomException('Kupón není určen pro tuto službu', ['notValidService' => true]);
            }
        }

        if ($customerRule) {
            if (!$user) {
                throw new CustomException('Kupón vyžaduje autorizaci', ['missingAuthorization' => true]);
            }
            if (!in_array($user->id, $customerRule)) {
                throw new CustomException('Kupón není určen pro tohoto uživatele', ['notValidUser' => true]);
            }
        }

        return [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'name' => $coupon->name,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
            'description' => $coupon->description,
            'image' => $coupon->image,
        ];
    }
}
