<?php

function upgrade_module_0_1_1($module) {
    /* CHANGELOG */
    // [FIX]: Overrides entries with empty value on saving.
    return true;
}