<?php

namespace App;

abstract class Types
{
    const HomeFood = 1;
    const InStore = 2;
    const Coupon = 3;
    const Delivery = 4;
}

abstract class Actions
{
    const Order = 1;
    const OrderNow = 2;
    const OrderDelivery = 3;
    const ClickHere = 4;
    const MoreInfo = 5;
    const Proceed = 6;
    const BuyCoupon = 7;
    const GetOffer = 8;
    const BuyNow = 9;
    const Website = 10;
}

abstract class Sources
{
    const StinJee = 1;
    const HungryHouse = 2;
}