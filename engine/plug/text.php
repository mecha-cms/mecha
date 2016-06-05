<?php


/**
 * Set Default Parser(s)
 * ---------------------
 */

// Convert text to ASCII character, `$mode` can be `dec` or `hex`
Text::parser('to_ascii', function($input, $mode = 'dec') {
    if( ! is_string($input)) return $input;
    $results = "";
    if(strtolower($mode[0]) === 'd') {
        for($i = 0, $count = strlen($input); $i < $count; ++$i) {
            $results .= '&#' . ord($input[$i]) . ';';
        }
    } else {
        for($i = 0, $count = strlen($input); $i < $count; ++$i) {
            $results .= '&#x' . dechex(ord($input[$i])) . ';';
        }
    }
    return $results;
});

// Obfuscate text (taken from the source code of PHP Markdown)
Text::parser('to_broken_entity', function($input) {
    if( ! is_string($input) || $input === "") return $input;
    $chars = preg_split('#(?<!^)(?!$)#', $input);
    $s = (int) abs(crc32($input) / strlen($input));
    foreach($chars as $k => $v) {
        $ord = ord($v);
        if($ord < 128) {
            $r = ($s * (1 + $k)) % 100;
            if($r > 90 && strpos('@"&>', $v) === false) {
                // do nothing ...
            } else if($r < 45) {
                $chars[$k] = '&#x' . dechex($ord) . ';';
            } else {
                $chars[$k] = '&#' . $ord . ';';
            }
        }
    }
    return implode("", $chars);
});

// De-obfuscate text
Text::parser('to_unite_entity', function($input) {
    return is_string($input) ? html_entity_decode($input, ENT_QUOTES, 'UTF-8') : $input;
});

// Convert decoded URL to encoded URL
Text::parser('to_encoded_url', function($input) {
    return is_string($input) ? urlencode($input) : $input;
});

// Convert encoded URL to decoded URL
Text::parser('to_decoded_url', function($input) {
    return is_string($input) ? urldecode($input) : $input;
});

// Convert decoded HTML to encoded HTML
Text::parser('to_encoded_html', function($input, $a = ENT_QUOTES) {
    return is_string($input) ? htmlspecialchars($input, $a) : $input;
});

// Convert encoded HTML to decoded HTML
Text::parser('to_decoded_html', function($input, $a = ENT_QUOTES) {
    return is_string($input) ? htmlspecialchars_decode($input, $a) : $input;
});

// Convert decoded JSON to encoded JSON
Text::parser('to_encoded_json', function($input) {
    return json_encode($input);
});

// Convert encoded JSON to decoded JSON
Text::parser('to_decoded_json', function($input, $a = false) {
    return is_string($input) && ! is_null(json_decode($input, $a)) ? json_decode($input, $a) : $input;
});

function do_slug($text, $join = '-', $exclude = 'a-zA-Z0-9') {
    $join_e = preg_quote($join, '#');
    $exclude .= $join_e;
    $from = explode(',', '¹,²,³,°,æ,ǽ,À,Á,Â,Ã,Å,Ǻ,Ă,Ǎ,Æ,Ǽ,à,á,â,ã,å,ǻ,ă,ǎ,ª,@,Ĉ,Ċ,ĉ,ċ,©,Ð,Đ,ð,đ,È,É,Ê,Ë,Ĕ,Ė,è,é,ê,ë,ĕ,ė,ƒ,Ĝ,Ġ,ĝ,ġ,Ĥ,Ħ,ĥ,ħ,Ì,Í,Î,Ï,Ĩ,Ĭ,Ǐ,Į,Ĳ,ì,í,î,ï,ĩ,ĭ,ǐ,į,ĳ,Ĵ,ĵ,Ĺ,Ľ,Ŀ,ĺ,ľ,ŀ,Ñ,ñ,ŉ,Ò,Ô,Õ,Ō,Ŏ,Ǒ,Ő,Ơ,Ø,Ǿ,Œ,ò,ô,õ,ō,ŏ,ǒ,ő,ơ,ø,ǿ,º,œ,Ŕ,Ŗ,ŕ,ŗ,Ŝ,Ș,ŝ,ș,ſ,Ţ,Ț,Ŧ,Þ,ţ,ț,ŧ,þ,Ù,Ú,Û,Ũ,Ŭ,Ű,Ų,Ư,Ǔ,Ǖ,Ǘ,Ǚ,Ǜ,ù,ú,û,ũ,ŭ,ű,ų,ư,ǔ,ǖ,ǘ,ǚ,ǜ,Ŵ,ŵ,Ý,Ÿ,Ŷ,ý,ÿ,ŷ,Ъ,Ь,А,Б,Ц,Ч,Д,Е,Ё,Э,Ф,Г,Х,И,Й,Я,Ю,К,Л,М,Н,О,П,Р,С,Ш,Щ,Т,У,В,Ы,З,Ж,ъ,ь,а,б,ц,ч,д,е,ё,э,ф,г,х,и,й,я,ю,к,л,м,н,о,п,р,с,ш,щ,т,у,в,ы,з,ж,Ä,Ö,Ü,ß,ä,ö,ü,Ç,Ğ,İ,Ş,ç,ğ,ı,ş,Ā,Ē,Ģ,Ī,Ķ,Ļ,Ņ,Ū,ā,ē,ģ,ī,ķ,ļ,ņ,ū,Ґ,І,Ї,Є,ґ,і,ї,є,Č,Ď,Ě,Ň,Ř,Š,Ť,Ů,Ž,č,ď,ě,ň,ř,š,ť,ů,ž,Ą,Ć,Ę,Ł,Ń,Ó,Ś,Ź,Ż,ą,ć,ę,ł,ń,ó,ś,ź,ż,Α,Β,Γ,Δ,Ε,Ζ,Η,Θ,Ι,Κ,Λ,Μ,Ν,Ξ,Ο,Π,Ρ,Σ,Τ,Υ,Φ,Χ,Ψ,Ω,Ϊ,Ϋ,ά,έ,ή,ί,ΰ,α,β,γ,δ,ε,ζ,η,θ,ι,κ,λ,μ,ν,ξ,ο,π,ρ,ς,σ,τ,υ,φ,χ,ψ,ω,ϊ,ϋ,ό,ύ,ώ,ϐ,ϑ,ϒ,أ,ب,ت,ث,ج,ح,خ,د,ذ,ر,ز,س,ش,ص,ض,ط,ظ,ع,غ,ف,ق,ك,ل,م,ن,ه,و,ي,ạ,ả,ầ,ấ,ậ,ẩ,ẫ,ằ,ắ,ặ,ẳ,ẵ,ẹ,ẻ,ẽ,ề,ế,ệ,ể,ễ,ị,ỉ,ọ,ỏ,ồ,ố,ộ,ổ,ỗ,ờ,ớ,ợ,ở,ỡ,ụ,ủ,ừ,ứ,ự,ử,ữ,ỳ,ỵ,ỷ,ỹ,Ạ,Ả,Ầ,Ấ,Ậ,Ẩ,Ẫ,Ằ,Ắ,Ặ,Ẳ,Ẵ,Ẹ,Ẻ,Ẽ,Ề,Ế,Ệ,Ể,Ễ,Ị,Ỉ,Ọ,Ỏ,Ồ,Ố,Ộ,Ổ,Ỗ,Ờ,Ớ,Ợ,Ở,Ỡ,Ụ,Ủ,Ừ,Ứ,Ự,Ử,Ữ,Ỳ,Ỵ,Ỷ,Ỹ');
    $to = explode(',', '1,2,3,0,ae,ae,A,A,A,A,A,A,A,A,AE,AE,a,a,a,a,a,a,a,a,a,at,C,C,c,c,c,Dj,D,dj,d,E,E,E,E,E,E,e,e,e,e,e,e,f,G,G,g,g,H,H,h,h,I,I,I,I,I,I,I,I,IJ,i,i,i,i,i,i,i,i,ij,J,j,L,L,L,l,l,l,N,n,n,O,O,O,O,O,O,O,O,O,O,OE,o,o,o,o,o,o,o,o,o,o,o,oe,R,R,r,r,S,S,s,s,s,T,T,T,TH,t,t,t,th,U,U,U,U,U,U,U,U,U,U,U,U,U,u,u,u,u,u,u,u,u,u,u,u,u,u,W,w,Y,Y,Y,y,y,y,,,A,B,C,Ch,D,E,E,E,F,G,H,I,J,Ja,Ju,K,L,M,N,O,P,R,S,Sh,Shch,T,U,V,Y,Z,Zh,,,a,b,c,ch,d,e,e,e,f,g,h,i,j,ja,ju,k,l,m,n,o,p,r,s,sh,shch,t,u,v,y,z,zh,AE,OE,UE,ss,ae,oe,ue,C,G,I,S,c,g,i,s,A,E,G,I,K,L,N,U,a,e,g,i,k,l,n,u,G,I,Ji,Ye,g,i,ji,ye,C,D,E,N,R,S,T,U,Z,c,d,e,n,r,s,t,u,z,A,C,E,L,N,O,S,Z,Z,a,c,e,l,n,o,s,z,z,A,B,G,D,E,Z,E,Th,I,K,L,M,N,X,O,P,R,S,T,Y,Ph,Ch,Ps,O,I,Y,a,e,e,i,Y,a,b,g,d,e,z,e,th,i,k,l,m,n,x,o,p,r,s,s,t,y,ph,ch,ps,o,i,y,o,y,o,b,th,Y,a,b,t,th,g,h,kh,d,th,r,z,s,sh,s,d,t,th,aa,gh,f,k,k,l,m,n,h,o,y,a,a,a,a,a,a,a,a,a,a,a,a,e,e,e,e,e,e,e,e,i,i,o,o,o,o,o,o,o,o,o,o,o,o,u,u,u,u,u,u,u,y,y,y,y,A,A,A,A,A,A,A,A,A,A,A,A,E,E,E,E,E,E,E,E,I,I,O,O,O,O,O,O,O,O,O,O,O,O,U,U,U,U,U,U,U,Y,Y,Y,Y');
    $slug = str_replace($from, $to, $text);
    $slug = preg_replace(
        array(
            '#<.*?>|&(?:[a-z0-9]+|\#[0-9]+|\#x[a-f0-9]+);#i',
            '#[^' . $exclude . ']#',
            '#' . $join_e . '+#',
            '#^' . $join_e . '|' . $join_e . '$#'
        ),
        array(
            $join,
            $join,
            $join,
            ""
        ),
    $slug);
    return ! empty($slug) ? $slug : str_repeat($join, 2);
}

// Convert text to slug pattern
Text::parser('to_slug', function($input, $join = '-') {
    return is_string($input) ? strtolower(do_slug($input, $join)) : $input;
});

// Convert text to safe file name pattern
Text::parser('to_safe_file_name', function($input, $lower = true) {
    return is_string($input) ? do_slug($lower ? strtolower($input) : $input, '-', '\w.') : $input;
});

// Convert text to safe folder name pattern
Text::parser('to_safe_folder_name', function($input, $lower = true) {
    return Text::parse($input, '->safe_file_name', $lower);
});

// Convert text to safe path name pattern
Text::parser('to_safe_path_name', function($input, $lower = true) {
    if( ! is_string($input)) return $input;
    $s = '-' . DS;
    $input = str_replace(array('\\', '/', '\\\\', '//'), array(DS, DS, $s, $s), $input);
    return do_slug($lower ? strtolower($input) : $input, '-', '\w.\\\/');
});

// Convert HTML/slug pattern to plain text
Text::parser('to_text', function($input, $tags = "", $no_break = true) {
    if( ! is_string($input)) return $input;
    // Should be a HTML input
    if(strpos($input, '<') !== false || strpos($input, ' ') !== false) {
        return preg_replace($no_break ? '#\s+#' : '# +#', ' ', trim(strip_tags($input, $tags)));
    }
    // 1. Replace `+` to ` `
    // 2. Replace `-` to ` `
    // 3. Replace `-----` to ` - `
    // 3. Replace `---` to `-`
    return preg_replace(
        array(
            '#^(\.|_{2})#', // remove `.` and `__` prefix on file name
            '#-{5}#',
            '#-{3}#',
            '#-#',
            '#\s+#',
            '#' . X . '#'
        ),
        array(
            "",
            ' ' . X . ' ',
            X,
            ' ',
            ' ',
            '-'
        ),
    urldecode($input));
});

// Convert text to array key pattern
Text::parser('to_array_key', function($input, $lower = false) {
    if( ! is_string($input)) return $input;
    $input = do_slug($lower ? strtolower($input) : $input, '_');
    $input = preg_replace('#^[\d_]+#', "", $input); // invalid if prefixed by number(s)
    return trim($input, '_') === "" ? '__' : $input;
});

// Convert plain text to HTML
Text::parser('to_html', function($input) {
    return $input; // Suppose that there aren't any HTML parser engine ...
});

// Convert `foo_bar_baz` to `FooBarBaz`
Text::parser('to_pascal_case', function($input, $join = '_ ') {
    if( ! is_string($input)) return $input;
    $input = ucwords(str_replace(str_split($join), ' ', $input));
    return str_replace(' ', "", $input);
});

// Convert `foo_bar_baz` to `fooBarBaz`
Text::parser('to_camel_case', function($input, $join = '_ ') {
    return is_string($input) ? lcfirst(Text::parse($input, '->pascal_case', $join)) : $input;
});

// Convert `FooBarBaz` to `foo_bar_baz`
Text::parser('to_snake_case', function($input, $join = '_', $lower = true) {
    if( ! is_string($input)) return $input;
    $input = preg_replace('#([a-z0-9\x{00E0}-\x{00FC}])([A-Z\x{00E0}-\x{00FC}])#u', '$1' . $join . '$2', $input);
    return $lower ? strtolower($input) : $input;
});

// Convert plain text to fake title
Text::parser('to_title', function($input) {
    return ucwords(Text::parse($input, '->text'));
});