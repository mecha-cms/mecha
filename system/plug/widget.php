<?php

// DEPRECATED. Please use `Widget::recentArticle()`
Widget::plug('recentPost', function($total = 7) {
    return Widget::recentArticle($total);
});

// DEPRECATED. Please use `Widget::randomArticle()`
Widget::plug('randomPost', function($total = 7) {
    return Widget::randomArticle($total);
});

// DEPRECATED. Please use `Widget::relatedArticle()`
Widget::plug('relatedPost', function($total = 7) {
    return Widget::relatedArticle($total);
});