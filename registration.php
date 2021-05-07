<?php

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Gifty_GiftCard',  isset($file) && realpath($file) == __FILE__ ? dirname($file) : __DIR__);
