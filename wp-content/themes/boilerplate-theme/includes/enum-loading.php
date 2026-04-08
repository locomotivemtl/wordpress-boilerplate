<?php

namespace Theme;

enum Loading : string {
    case LAZY = 'lazy';
    case EAGER = 'eager';
}
