<?php

// DEPRECATED. Please use `Widget::recentArticle()`
Widget::add('recentPost', function($total = 7) {
    return Widget::recentArticle($total);
});

// DEPRECATED. Please use `Widget::randomArticle()`
Widget::add('randomPost', function($total = 7) {
    return Widget::randomArticle($total);
});

// DEPRECATED. Please use `Widget::relatedArticle()`
Widget::add('relatedPost', function($total = 7) {
    return Widget::relatedArticle($total);
});