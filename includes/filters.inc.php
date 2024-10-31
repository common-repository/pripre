<?php
// 縦書きのための文字変換を無効にする
global $pripre_tate_filter_disable;
$pripre_tate_filter_disable = FALSE;

/**
 * 縦書きのために文字を変換します。
 * 
 * @param string $buff
 * @return string
 */
function pripre_tate_filter($buff) {
	global $pripre_tate_filter_disable;
	
	if ($pripre_tate_filter_disable) {
		return $buff;
	}
	mb_regex_encoding('UTF-8');
    mb_internal_encoding("UTF-8");

    $nums = "0123456789";
    $from = "0123456789０１２３４５６７８９“”．,－―";
    $to = "０１２３４５６７８９〇一二三四五六七八九〝〟・、―─";
    
    $state2 = 0;
    // 行ごとに処理する
    $textarr = explode("\n", $buff);
    foreach($textarr as &$buff) {
    	
    if ($state2 == 1) {
    	if (mb_substr($buff, 0, 38) == '</div><span class="_pre_end"></span>') {
    		$state2 = 0;
    		$buff = '</div>';
       	}
    	continue;
    }
    else if (mb_substr($buff, 0, 18) == '<div class="_pre">') {
    	$state2 = 1;
    	$buff = '<div>';
    	continue;
    }
    
    $result = '';
    $state = 0;
    for ($i = 0; $i < mb_strlen($buff); ++$i) {
        $c = mb_substr($buff, $i, 1);
        if (mb_strpos($nums, $c) !== FALSE) {
            if ($state == 0) {
                $state = 2;
                $pos = $i;
                continue;
            } else if ($state == 3) {
                
            } else if ($i - $pos >= 2) {
                $i = $pos - 1;
                $state = 3;
                continue;
            } else {
                continue;
            }
        } else {
        	// 縦中横
            if ($state == 2) {
                if ($i - $pos >= 2) {
                    $result = $result.'<span class="_tcy">';
                    $result = $result.mb_substr($buff, $pos, $i - $pos);
                    $result = $result.'</span>';
                } else {
                    $i = $pos - 1;
                    $state = 3;
                    continue;
                }
            }
            $state = 0;
        }
        if ($c == '<') {
        	// 変換なし
            if (mb_substr($buff, $i + 1, 18) == 'span class="_pre">') {
                for (; $i < mb_strlen($buff); ++$i) {
                    $c = mb_substr($buff, $i, 1);
                    $result = $result.$c;
                    if ($c == '<' && mb_substr($buff, $i + 1, 5) == '/span') {
                        break;
                    }
                }
                continue;
            }
            for (; $i < mb_strlen($buff); ++$i) {
                $c = mb_substr($buff, $i, 1);
                $result = $result.$c;
                if ($c == '>') {
                    break;
                }
            }
            continue;
        }
        // http:で始まるアドレス
        else if ($c == 'h' && mb_substr($buff, $i + 1, 4) == 'ttp:') {
            for (; $i < mb_strlen($buff); ++$i) {
                $c = mb_substr($buff, $i, 1);
                if (mb_strpos('!#$%&*+-./0123456789:;=?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_abcdefghijklmnopqrstuvwxyz[|]~', $c) === FALSE) {
                    break;
                }
                $result = $result.$c;
            }
        }
        // エンティティ
        else if ($c == '&') {
        	$xi = $i;
        	$cnt = 0;
        	for (; $i < mb_strlen($buff); ++$i) {
        		$c = mb_substr($buff, $i, 1);
        		if ($c == ';' || $cnt >= 16) {
        			break;
        		}
        		++$cnt;
        	}
        	$i = $xi;
        	if ($cnt < 16) {
	        	for (; $i < mb_strlen($buff); ++$i) {
	        		$c = mb_substr($buff, $i, 1);
	        		if ($c == ';') {
	        			break;
	        		}
	        		$result = $result.$c;
	        	}
        	}
        }
        
        $ix = mb_strpos($from, $c);
        if ($ix !== FALSE) {
            $c = mb_substr($to, $ix, 1);
        }
        $result = $result.$c;
    }
    $buff = $result;
    }
    return implode("\n", $textarr);
}

define("PRIPRE_RB", "<ruby>");
define("PRIPRE_RTB", "<rp>（</rp><rt>");
define("PRIPRE_RTE", "</rt><rp>）</rp></ruby>");

function pripre_is_kanji($c) {
	return preg_match("/^[一-龠]+$/u", $c) || $c == '々';
}

function pripre_substr_replace(&$buff, $replace, $start, $len) {
	$buff = mb_substr($buff, 0, $start) . $replace . mb_substr($buff, $start + $len);
}

/**
 * ルビ、圏点などの処理をします。
 * 
 * @param string $buff
 * @return string
 */
function pripre_text_filter($buff) {
    mb_regex_encoding('UTF-8');
    mb_internal_encoding("UTF-8");

    // 行ごとに処理する
    $textarr = explode("\n", $buff);
    foreach($textarr as &$buff) {

    if (mb_ereg('［(.+)＜］', $buff, $regs)) {
    	if ($regs[1] == '無変換') {
    		$buff = '<div class="_pre">';
    	}
    	else if (mb_substr($regs[1], 0, 1) == '図') {
    		$attr = mb_substr($regs[1], 1);
    		$class = '';
    		if (mb_ereg('align=&#8221;([a-zA-Z]+)&#8221;', $attr, $regs)) {
    			$class = ' '.$regs[1];
    		}
    		$width = '';
    	    if (mb_ereg('width=&#8221;([\\.0-9]+[a-zA-Z]+)&#8221;', $attr, $regs)) {
    			$width = $regs[1];
    		}
    	    else if (mb_ereg('width=&#8221;([\\.0-9]+)&#8243;', $attr, $regs)) {
    			$width = $regs[1].'px';
    		}
    		$buff = '<div class="wp-caption';
    		$buff .= $class;
    		$buff .= '"';
    		if (!empty($width)) {
    			$buff .= ' style="width:';
    			$buff .= $width;
    			$buff .= ';"';
    		}
    		$buff .= '>';
    	}
    }
    else if (mb_ereg('［＞(.+)］', $buff, $regs)) {
    	if ($regs[1] == '無変換') {
    		$buff = '</div><span class="_pre_end"></span>';
    	}
    	else if ($regs[1] == '図') {
    		$buff = '</div>';
    	}
    }
    
    for ($i = 0; $i < mb_strlen($buff); ++$i) {
        $c = mb_substr($buff, $i, 1);

        if ($c == '《') {
            // ルビ
            $j = $i + 1;
            $blen = mb_strlen($buff);
            for (; $j < $blen; ++$j) {
                $d = mb_substr($buff, $j, 1);
                if ($d == '》') {
                    break;
                }
            }
            if ($j == $blen) {
                continue;
            }
            $scan_kanji = TRUE;
            $k = $i;
            for (; $k > 0; --$k) {
                $d = mb_substr($buff, $k - 1, 1);
                if ($d == '｜') {
                    pripre_substr_replace($buff, "", $k - 1, 1);
                    --$k;
                    --$j;
                    --$i;
                    break;
                }
                if ($scan_kanji
                        && (!pripre_is_kanji($d) || $d == '》')) {
                    if ($k == $i) {
                        $scan_kanji = FALSE;
                    } else {
                        break;
                    }
                }
            }
            if ($i == $k) {
                continue;
            }
            pripre_substr_replace($buff, "", $i, 1);
            --$j;
            pripre_substr_replace($buff, "", $j, 1);
            pripre_substr_replace($buff, PRIPRE_RB, $k, 0);
            $i += mb_strlen(PRIPRE_RB);
            $j += mb_strlen(PRIPRE_RB);
            pripre_substr_replace($buff, PRIPRE_RTB, $i, 0);
            $j += mb_strlen(PRIPRE_RTB);
            $i = $j;
            pripre_substr_replace($buff, PRIPRE_RTE, $i, 0);
            $i += mb_strlen(PRIPRE_RTE);
        } else if ($c == '［' && mb_substr($buff, $i + 1, 1) == '＊') {
        	// タグ
        	$blen = mb_strlen($buff);
        	$j = $i + 1;
        	$op = mb_substr($buff, $i + 2, 4);
        	switch($op) {
        		case "改ページ":
        			$tag = '<span class="_pagebreak"></span>';
        			pripre_substr_replace($buff, $tag, $i, 7);
        			$i += mb_strlen($tag);
        			continue 2;
        		case "改カラム":
        			$tag = '<span class="_columnbreak"></span>';
        			pripre_substr_replace($buff, $tag, $i, 7);
        			$i += mb_strlen($tag);
        			continue 2;
        	}
        } else if ($c == '［' && mb_substr($buff, $i + 1, 1) == '＃') {
        	// 区間タグ
            $blen = mb_strlen($buff);
            $j = $i + 1;
            for (; $j < $blen; ++$j) {
                $d = mb_substr($buff, $j, 1);
                if ($d == '］') {
                    break;
                }
            }
            if ($j == $blen) {
                continue;
            }
            $k = $i;
            for (; $k > 0; --$k) {
                $d = mb_substr($buff, $k - 1, 1);
                if ($d == '｜') {
                    pripre_substr_replace($buff, "", $k - 1, 1);
                    --$k;
                    --$j;
                    --$i;
                    break;
                }
            }
            if ($i == $k) {
                continue;
            }
            $op = mb_substr($buff, $i + 2, 2);
            switch($op) {
	            case "圏点":
	           	if (mb_substr($buff, $i + 4, 1) == '］') {
	           		$emStart = "<span class=\"_em\">";
	           	}
	           	else {
	            	$emStart = "<span style=\"-epub-text-emphasis:'".
	            		mb_substr($buff, $i + 5, $j - ($i + 5))."'\">";
	           	}
	            $emEnd = "</span>";
	            pripre_substr_replace($buff, $emEnd, $i, ($j + 1) - $i);
	            $i += mb_strlen($emEnd);
	
	            pripre_substr_replace($buff, $emStart, $k, 0);
	            $i += mb_strlen($emStart);
	            continue 2;
            	
            	case "説明":
	           	$emStart = "<span class=\"wp-caption-text\">";
	            $emEnd = "</span>";
	            pripre_substr_replace($buff, $emEnd, $i, ($j + 1) - $i);
	            $i += mb_strlen($emEnd);
	
	            pripre_substr_replace($buff, $emStart, $k, 0);
	            $i += mb_strlen($emStart);
	            continue 2;
            }
            $op = mb_substr($buff, $i + 2, 3);
            switch($op) {
	            case "縦中横":
	           	$emStart = "<span class=\"_tcy\">";
	            $emEnd = "</span>";
	            pripre_substr_replace($buff, $emEnd, $i, ($j + 1) - $i);
	            $i += mb_strlen($emEnd);
	
	            pripre_substr_replace($buff, $emStart, $k, 0);
	            $i += mb_strlen($emStart);
	            continue 2;
            	            
	            case "無変換":
	           	$emStart = "<span class=\"_pre\">";
	            $emEnd = "</span>";
	            pripre_substr_replace($buff, $emEnd, $i, ($j + 1) - $i);
	            $i += mb_strlen($emEnd);
	
	            pripre_substr_replace($buff, $emStart, $k, 0);
	            $i += mb_strlen($emStart);
	            continue 2;
	            
	            case "非表示":
            	$emStart = "<span class=\"_hidden\">";
            	$emEnd = "</span>";
            	pripre_substr_replace($buff, $emEnd, $i, ($j + 1) - $i);
            	$i += mb_strlen($emEnd);
            
            	pripre_substr_replace($buff, $emStart, $k, 0);
            	$i += mb_strlen($emStart);
            	continue 2;
            }            
        }
    }
    $buff = mb_ereg_replace('<p>[:space:]*([‘“（〔［｛〈《「『【⦅〖«〝])', '<p class="_noindent">\\1', $buff);
    }
    return implode("\n", $textarr);
}

// HTML整形
function pripre_formalize_filter($buff) {
    $doc = new DOMDocument();
    $doc->loadHTML("<?xml encoding=\"UTF-8\"><body id=\"ROOT\">$buff</body>");
    $node = $doc->getElementById("ROOT");
    $buff = $doc->saveXML($node);
    $buff = substr($buff, 16, strlen($buff) - 23);
    return $buff;
}

/**
 * SVGアップロード許可
 * 
 * @param array $ext2type
 * @return array
 */
function pripre_ext2type($ext2type) {
	array_push($ext2type, array('image' => 'svg'));
	return $ext2type;
}

/**
 * SVGアップロード許可
 * 
 * @param string $mimes
 * @return array
 */
function pripre_mime_type($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
}

function pripre_style_groups($dirs) {
	$dir = pripre_get_base_dir();
	$dir = "$dir/templates/book";
	$dh = opendir($dir);
	while (($id = readdir($dh)) !== FALSE) {
		$file = "$dir/$id/info.xml";
		if (file_exists($file)) {
			$dirs[$id] = "$dir/$id";
		}
	}
	return $dirs;
}

function pripre_estyles($dirs) {
	$dir = pripre_get_base_dir();
	$dir = "$dir/templates/ebook";
	$dh = opendir($dir);
	while (($id = readdir($dh)) !== FALSE) {
		$file = "$dir/$id/info.xml";
		if (file_exists($file)) {
			$dirs[$id] = "$dir/$id";
		}
	}
	return $dirs;
}
?>