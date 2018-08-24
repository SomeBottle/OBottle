<?php
 define( 'MARKDOWN_VERSION', "1.0.2" ); define( 'MARKDOWNEXTRA_VERSION', "1.2.8" ); @define( 'MARKDOWN_EMPTY_ELEMENT_SUFFIX', " />"); @define( 'MARKDOWN_TAB_WIDTH', 4 ); @define( 'MARKDOWN_FN_LINK_TITLE', "" ); @define( 'MARKDOWN_FN_BACKLINK_TITLE', "" ); @define( 'MARKDOWN_FN_LINK_CLASS', "" ); @define( 'MARKDOWN_FN_BACKLINK_CLASS', "" ); @define( 'MARKDOWN_CODE_CLASS_PREFIX', "" ); @define( 'MARKDOWN_CODE_ATTR_ON_PRE', false ); @define( 'MARKDOWN_WP_POSTS', true ); @define( 'MARKDOWN_WP_COMMENTS', true ); @define( 'MARKDOWN_PARSER_CLASS', 'MarkdownExtra_Parser' ); function Markdown($text) { static $parser; if (!isset($parser)) { $parser_class = MARKDOWN_PARSER_CLASS; $parser = new $parser_class; } return $parser->transform($text); } if (isset($wp_version)) { if (MARKDOWN_WP_POSTS) { remove_filter('the_content', 'wpautop'); remove_filter('the_content_rss', 'wpautop'); remove_filter('the_excerpt', 'wpautop'); add_filter('the_content', 'mdwp_MarkdownPost', 6); add_filter('the_content_rss', 'mdwp_MarkdownPost', 6); add_filter('get_the_excerpt', 'mdwp_MarkdownPost', 6); add_filter('get_the_excerpt', 'trim', 7); add_filter('the_excerpt', 'mdwp_add_p'); add_filter('the_excerpt_rss', 'mdwp_strip_p'); remove_filter('content_save_pre', 'balanceTags', 50); remove_filter('excerpt_save_pre', 'balanceTags', 50); add_filter('the_content', 'balanceTags', 50); add_filter('get_the_excerpt', 'balanceTags', 9); } function mdwp_MarkdownPost($text) { static $parser; if (!$parser) { $parser_class = MARKDOWN_PARSER_CLASS; $parser = new $parser_class; } if (is_single() || is_page() || is_feed()) { $parser->fn_id_prefix = ""; } else { $parser->fn_id_prefix = get_the_ID() . "."; } return $parser->transform($text); } if (MARKDOWN_WP_COMMENTS) { remove_filter('comment_text', 'wpautop', 30); remove_filter('comment_text', 'make_clickable'); add_filter('pre_comment_content', 'Markdown', 6); add_filter('pre_comment_content', 'mdwp_hide_tags', 8); add_filter('pre_comment_content', 'mdwp_show_tags', 12); add_filter('get_comment_text', 'Markdown', 6); add_filter('get_comment_excerpt', 'Markdown', 6); add_filter('get_comment_excerpt', 'mdwp_strip_p', 7); global $mdwp_hidden_tags, $mdwp_placeholders; $mdwp_hidden_tags = explode(' ', '<p> </p> <pre> </pre> <ol> </ol> <ul> </ul> <li> </li>'); $mdwp_placeholders = explode(' ', str_rot13( 'pEj07ZbbBZ U1kqgh4w4p pre2zmeN6K QTi31t9pre ol0MP1jzJR '. 'ML5IjmbRol ulANi1NsGY J7zRLJqPul liA8ctl16T K9nhooUHli')); } function mdwp_add_p($text) { if (!preg_match('{^$|^<(p|ul|ol|dl|pre|blockquote)>}i', $text)) { $text = '<p>'.$text.'</p>'; $text = preg_replace('{\n{2,}}', "</p>\n\n<p>", $text); } return $text; } function mdwp_strip_p($t) { return preg_replace('{</?p>}i', '', $t); } function mdwp_hide_tags($text) { global $mdwp_hidden_tags, $mdwp_placeholders; return str_replace($mdwp_hidden_tags, $mdwp_placeholders, $text); } function mdwp_show_tags($text) { global $mdwp_hidden_tags, $mdwp_placeholders; return str_replace($mdwp_placeholders, $mdwp_hidden_tags, $text); } } function identify_modifier_markdown() { return array( 'name' => 'markdown', 'type' => 'modifier', 'nicename' => 'PHP Markdown Extra', 'description' => 'A text-to-HTML conversion tool for web writers', 'authors' => 'Michel Fortin and John Gruber', 'licence' => 'GPL', 'version' => MARKDOWNEXTRA_VERSION, 'help' => '<a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a> allows you to write using an easy-to-read, easy-to-write plain text format. Based on the original Perl version by <a href="http://daringfireball.net/">John Gruber</a>. <a href="http://michelf.ca/projects/php-markdown/">More...</a>', ); } function smarty_modifier_markdown($text) { return Markdown($text); } if (strcasecmp(substr(__FILE__, -16), "classTextile.php") == 0) { @include_once 'smartypants.php'; class Textile { function TextileThis($text, $lite='', $encode='') { if ($lite == '' && $encode == '') $text = Markdown($text); if (function_exists('SmartyPants')) $text = SmartyPants($text); return $text; } function TextileRestricted($text, $lite='', $noimage='') { return $this->TextileThis($text, $lite); } function blockLite($text) { return $text; } } } class Markdown_Parser { var $empty_element_suffix = MARKDOWN_EMPTY_ELEMENT_SUFFIX; var $tab_width = MARKDOWN_TAB_WIDTH; var $no_markup = false; var $no_entities = false; var $predef_urls = array(); var $predef_titles = array(); var $nested_brackets_depth = 6; var $nested_brackets_re; var $nested_url_parenthesis_depth = 4; var $nested_url_parenthesis_re; var $escape_chars = '\`*_{}[]()>#+-.!'; var $escape_chars_re; function Markdown_Parser() { $this->_initDetab(); $this->prepareItalicsAndBold(); $this->nested_brackets_re = str_repeat('(?>[^\[\]]+|\[', $this->nested_brackets_depth). str_repeat('\])*', $this->nested_brackets_depth); $this->nested_url_parenthesis_re = str_repeat('(?>[^()\s]+|\(', $this->nested_url_parenthesis_depth). str_repeat('(?>\)))*', $this->nested_url_parenthesis_depth); $this->escape_chars_re = '['.preg_quote($this->escape_chars).']'; asort($this->document_gamut); asort($this->block_gamut); asort($this->span_gamut); } var $urls = array(); var $titles = array(); var $html_hashes = array(); var $in_anchor = false; function setup() { $this->urls = $this->predef_urls; $this->titles = $this->predef_titles; $this->html_hashes = array(); $this->in_anchor = false; } function teardown() { $this->urls = array(); $this->titles = array(); $this->html_hashes = array(); } function transform($text) { $this->setup(); $text = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text); $text = preg_replace('{\r\n?}', "\n", $text); $text .= "\n\n"; $text = $this->detab($text); $text = $this->hashHTMLBlocks($text); $text = preg_replace('/^[ ]+$/m', '', $text); foreach ($this->document_gamut as $method => $priority) { $text = $this->$method($text); } $this->teardown(); return $text . "\n"; } var $document_gamut = array( "stripLinkDefinitions" => 20, "runBasicBlockGamut" => 30, ); function stripLinkDefinitions($text) { $less_than_tab = $this->tab_width - 1; $text = preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>			# url = $2
							|
							  (\S+?)			# url = $3
							)
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)			# lookbehind for whitespace
								["(]
								(.*?)			# title = $4
								[")]
								[ ]*
							)?	# title is optional
							(?:\n+|\Z)
			}xm', array(&$this, '_stripLinkDefinitions_callback'), $text); return $text; } function _stripLinkDefinitions_callback($matches) { $link_id = strtolower($matches[1]); $url = $matches[2] == '' ? $matches[3] : $matches[2]; $this->urls[$link_id] = $url; $this->titles[$link_id] =& $matches[4]; return ''; } function hashHTMLBlocks($text) { if ($this->no_markup) return $text; $less_than_tab = $this->tab_width - 1; $block_tags_a_re = 'ins|del'; $block_tags_b_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|'. 'script|noscript|form|fieldset|iframe|math|svg|'. 'article|section|nav|aside|hgroup|header|footer|'. 'figure'; $nested_tags_level = 4; $attr = '
			(?>				# optional tag attributes
			  \s			# starts with whitespace
			  (?>
				[^>"/]+		# text outside quotes
			  |
				/+(?!>)		# slash not followed by ">"
			  |
				"[^"]*"		# text inside double quotes (tolerate ">")
			  |
				\'[^\']*\'	# text inside single quotes (tolerate ">")
			  )*
			)?	
			'; $content = str_repeat('
				(?>
				  [^<]+			# content without tag
				|
				  <\2			# nested opening tag
					'.$attr.'	# attributes
					(?>
					  />
					|
					  >', $nested_tags_level). '.*?'. str_repeat('
					  </\2\s*>	# closing nested tag
					)
				  |				
					<(?!/\2\s*>	# other tags with a different name
				  )
				)*', $nested_tags_level); $content2 = str_replace('\2', '\3', $content); $text = preg_replace_callback('{(?>
			(?>
				(?<=\n\n)		# Starting after a blank line
				|				# or
				\A\n?			# the beginning of the doc
			)
			(						# save in $1

			  # Match from `\n<tag>` to `</tag>\n`, handling nested tags 
			  # in between.
					
						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_b_re.')# start tag = $2
						'.$attr.'>			# attributes followed by > and \n
						'.$content.'		# content, support nesting
						</\2>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document

			| # Special version for tags of group a.

						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_a_re.')# start tag = $3
						'.$attr.'>[ ]*\n	# attributes followed by >
						'.$content2.'		# content, support nesting
						</\3>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document
					
			| # Special case just for <hr />. It was easier to make a special 
			  # case than to make the other regex more complicated.
			
						[ ]{0,'.$less_than_tab.'}
						<(hr)				# start tag = $2
						'.$attr.'			# attributes
						/?>					# the matching end tag
						[ ]*
						(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # Special case for standalone HTML comments:
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<!-- .*? -->
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # PHP and ASP-style processor instructions (<? and <%)
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<([?%])			# $2
						.*?
						\2>
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
					
			)
			)}Sxmi', array(&$this, '_hashHTMLBlocks_callback'), $text); return $text; } function _hashHTMLBlocks_callback($matches) { $text = $matches[1]; $key = $this->hashBlock($text); return "\n\n$key\n\n"; } function hashPart($text, $boundary = 'X') { $text = $this->unhash($text); static $i = 0; $key = "$boundary\x1A" . ++$i . $boundary; $this->html_hashes[$key] = $text; return $key; } function hashBlock($text) { return $this->hashPart($text, 'B'); } var $block_gamut = array( "doHeaders" => 10, "doHorizontalRules" => 20, "doLists" => 40, "doCodeBlocks" => 50, "doBlockQuotes" => 60, ); function runBlockGamut($text) { $text = $this->hashHTMLBlocks($text); return $this->runBasicBlockGamut($text); } function runBasicBlockGamut($text) { foreach ($this->block_gamut as $method => $priority) { $text = $this->$method($text); } $text = $this->formParagraphs($text); return $text; } function doHorizontalRules($text) { return preg_replace( '{
				^[ ]{0,3}	# Leading space
				([-*_])		# $1: First marker
				(?>			# Repeated marker group
					[ ]{0,2}	# Zero, one, or two spaces.
					\1			# Marker character
				){2,}		# Group repeated at least twice
				[ ]*		# Tailing spaces
				$			# End of line.
			}mx', "\n".$this->hashBlock("<hr$this->empty_element_suffix")."\n", $text); } var $span_gamut = array( "parseSpan" => -30, "doImages" => 10, "doAnchors" => 20, "doAutoLinks" => 30, "encodeAmpsAndAngles" => 40, "doItalicsAndBold" => 50, "doHardBreaks" => 60, ); function runSpanGamut($text) { foreach ($this->span_gamut as $method => $priority) { $text = $this->$method($text); } return $text; } function doHardBreaks($text) { return preg_replace_callback('/ {2,}\n/', array(&$this, '_doHardBreaks_callback'), $text); } function _doHardBreaks_callback($matches) { return $this->hashPart("<br$this->empty_element_suffix\n"); } function doAnchors($text) { if ($this->in_anchor) return $text; $this->in_anchor = true; $text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]
			)
			}xs', array(&$this, '_doAnchors_reference_callback'), $text); $text = preg_replace_callback('{
			(				# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]
			  \(			# literal paren
				[ \n]*
				(?:
					<(.+?)>	# href = $3
				|
					('.$this->nested_url_parenthesis_re.')	# href = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# Title = $7
				  \6		# matching quote
				  [ \n]*	# ignore any spaces/tabs between closing quote and )
				)?			# title is optional
			  \)
			)
			}xs', array(&$this, '_doAnchors_inline_callback'), $text); $text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs', array(&$this, '_doAnchors_reference_callback'), $text); $this->in_anchor = false; return $text; } function _doAnchors_reference_callback($matches) { $whole_match = $matches[1]; $link_text = $matches[2]; $link_id =& $matches[3]; if ($link_id == "") { $link_id = $link_text; } $link_id = strtolower($link_id); $link_id = preg_replace('{[ ]?\n}', ' ', $link_id); if (isset($this->urls[$link_id])) { $url = $this->urls[$link_id]; $url = $this->encodeAttribute($url); $result = "<a href=\"$url\""; if ( isset( $this->titles[$link_id] ) ) { $title = $this->titles[$link_id]; $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $link_text = $this->runSpanGamut($link_text); $result .= ">$link_text</a>"; $result = $this->hashPart($result); } else { $result = $whole_match; } return $result; } function _doAnchors_inline_callback($matches) { $whole_match = $matches[1]; $link_text = $this->runSpanGamut($matches[2]); $url = $matches[3] == '' ? $matches[4] : $matches[3]; $title =& $matches[7]; $url = $this->encodeAttribute($url); $result = "<a href=\"$url\""; if (isset($title)) { $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $link_text = $this->runSpanGamut($link_text); $result .= ">$link_text</a>"; return $this->hashPart($result); } function doImages($text) { $text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]

			)
			}xs', array(&$this, '_doImages_reference_callback'), $text); $text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->nested_url_parenthesis_re.')	# src url = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# title = $7
				  \6		# matching quote
				  [ \n]*
				)?			# title is optional
			  \)
			)
			}xs', array(&$this, '_doImages_inline_callback'), $text); return $text; } function _doImages_reference_callback($matches) { $whole_match = $matches[1]; $alt_text = $matches[2]; $link_id = strtolower($matches[3]); if ($link_id == "") { $link_id = strtolower($alt_text); } $alt_text = $this->encodeAttribute($alt_text); if (isset($this->urls[$link_id])) { $url = $this->encodeAttribute($this->urls[$link_id]); $result = "<img src=\"$url\" alt=\"$alt_text\""; if (isset($this->titles[$link_id])) { $title = $this->titles[$link_id]; $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $result .= $this->empty_element_suffix; $result = $this->hashPart($result); } else { $result = $whole_match; } return $result; } function _doImages_inline_callback($matches) { $whole_match = $matches[1]; $alt_text = $matches[2]; $url = $matches[3] == '' ? $matches[4] : $matches[3]; $title =& $matches[7]; $alt_text = $this->encodeAttribute($alt_text); $url = $this->encodeAttribute($url); $result = "<img src=\"$url\" alt=\"$alt_text\""; if (isset($title)) { $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $result .= $this->empty_element_suffix; return $this->hashPart($result); } function doHeaders($text) { $text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx', array(&$this, '_doHeaders_callback_setext'), $text); $text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				\n+
			}xm', array(&$this, '_doHeaders_callback_atx'), $text); return $text; } function _doHeaders_callback_setext($matches) { if ($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1])) return $matches[0]; $level = $matches[2]{0} == '=' ? 1 : 2; $block = "<h$level>".$this->runSpanGamut($matches[1])."</h$level>"; return "\n" . $this->hashBlock($block) . "\n\n"; } function _doHeaders_callback_atx($matches) { $level = strlen($matches[1]); $block = "<h$level>".$this->runSpanGamut($matches[2])."</h$level>"; return "\n" . $this->hashBlock($block) . "\n\n"; } function doLists($text) { $less_than_tab = $this->tab_width - 1; $marker_ul_re = '[*+-]'; $marker_ol_re = '\d+[\.]'; $marker_any_re = "(?:$marker_ul_re|$marker_ol_re)"; $markers_relist = array( $marker_ul_re => $marker_ol_re, $marker_ol_re => $marker_ul_re, ); foreach ($markers_relist as $marker_re => $other_marker_re) { $whole_list_re = '
				(								# $1 = whole list
				  (								# $2
					([ ]{0,'.$less_than_tab.'})	# $3 = number of spaces
					('.$marker_re.')			# $4 = first list item marker
					[ ]+
				  )
				  (?s:.+?)
				  (								# $5
					  \z
					|
					  \n{2,}
					  (?=\S)
					  (?!						# Negative lookahead for another list item marker
						[ ]*
						'.$marker_re.'[ ]+
					  )
					|
					  (?=						# Lookahead for another kind of list
					    \n
						\3						# Must have the same indentation
						'.$other_marker_re.'[ ]+
					  )
				  )
				)
			'; if ($this->list_level) { $text = preg_replace_callback('{
						^
						'.$whole_list_re.'
					}mx', array(&$this, '_doLists_callback'), $text); } else { $text = preg_replace_callback('{
						(?:(?<=\n)\n|\A\n?) # Must eat the newline
						'.$whole_list_re.'
					}mx', array(&$this, '_doLists_callback'), $text); } } return $text; } function _doLists_callback($matches) { $marker_ul_re = '[*+-]'; $marker_ol_re = '\d+[\.]'; $marker_any_re = "(?:$marker_ul_re|$marker_ol_re)"; $list = $matches[1]; $list_type = preg_match("/$marker_ul_re/", $matches[4]) ? "ul" : "ol"; $marker_any_re = ( $list_type == "ul" ? $marker_ul_re : $marker_ol_re ); $list .= "\n"; $result = $this->processListItems($list, $marker_any_re); $result = $this->hashBlock("<$list_type>\n" . $result . "</$list_type>"); return "\n". $result ."\n\n"; } var $list_level = 0; function processListItems($list_str, $marker_any_re) { $this->list_level++; $list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str); $list_str = preg_replace_callback('{
			(\n)?							# leading line = $1
			(^[ ]*)							# leading whitespace = $2
			('.$marker_any_re.'				# list marker and space = $3
				(?:[ ]+|(?=\n))	# space only required if item is not empty
			)
			((?s:.*?))						# list item text   = $4
			(?:(\n+(?=\n))|\n)				# tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_any_re.') (?:[ ]+|(?=\n))))
			}xm', array(&$this, '_processListItems_callback'), $list_str); $this->list_level--; return $list_str; } function _processListItems_callback($matches) { $item = $matches[4]; $leading_line =& $matches[1]; $leading_space =& $matches[2]; $marker_space = $matches[3]; $tailing_blank_line =& $matches[5]; if ($leading_line || $tailing_blank_line || preg_match('/\n{2,}/', $item)) { $item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item; $item = $this->runBlockGamut($this->outdent($item)."\n"); } else { $item = $this->doLists($this->outdent($item)); $item = preg_replace('/\n+$/', '', $item); $item = $this->runSpanGamut($item); } return "<li>" . $item . "</li>\n"; } function doCodeBlocks($text) { $text = preg_replace_callback('{
				(?:\n\n|\A\n?)
				(	            # $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{'.$this->tab_width.'}  # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,'.$this->tab_width.'}\S)|\Z)	# Lookahead for non-space at line-start, or end of doc
			}xm', array(&$this, '_doCodeBlocks_callback'), $text); return $text; } function _doCodeBlocks_callback($matches) { $codeblock = $matches[1]; $codeblock = $this->outdent($codeblock); $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES); $codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock); $codeblock = "<pre><code>$codeblock\n</code></pre>"; return "\n\n".$this->hashBlock($codeblock)."\n\n"; } function makeCodeSpan($code) { $code = htmlspecialchars(trim($code), ENT_NOQUOTES); return $this->hashPart("<code>$code</code>"); } var $em_relist = array( '' => '(?:(?<!\*)\*(?!\*)|(?<!_)_(?!_))(?=\S|$)(?![\.,:;]\s)', '*' => '(?<=\S|^)(?<!\*)\*(?!\*)', '_' => '(?<=\S|^)(?<!_)_(?!_)', ); var $strong_relist = array( '' => '(?:(?<!\*)\*\*(?!\*)|(?<!_)__(?!_))(?=\S|$)(?![\.,:;]\s)', '**' => '(?<=\S|^)(?<!\*)\*\*(?!\*)', '__' => '(?<=\S|^)(?<!_)__(?!_)', ); var $em_strong_relist = array( '' => '(?:(?<!\*)\*\*\*(?!\*)|(?<!_)___(?!_))(?=\S|$)(?![\.,:;]\s)', '***' => '(?<=\S|^)(?<!\*)\*\*\*(?!\*)', '___' => '(?<=\S|^)(?<!_)___(?!_)', ); var $em_strong_prepared_relist; function prepareItalicsAndBold() { foreach ($this->em_relist as $em => $em_re) { foreach ($this->strong_relist as $strong => $strong_re) { $token_relist = array(); if (isset($this->em_strong_relist["$em$strong"])) { $token_relist[] = $this->em_strong_relist["$em$strong"]; } $token_relist[] = $em_re; $token_relist[] = $strong_re; $token_re = '{('. implode('|', $token_relist) .')}'; $this->em_strong_prepared_relist["$em$strong"] = $token_re; } } } function doItalicsAndBold($text) { $token_stack = array(''); $text_stack = array(''); $em = ''; $strong = ''; $tree_char_em = false; while (1) { $token_re = $this->em_strong_prepared_relist["$em$strong"]; $parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE); $text_stack[0] .= $parts[0]; $token =& $parts[1]; $text =& $parts[2]; if (empty($token)) { while ($token_stack[0]) { $text_stack[1] .= array_shift($token_stack); $text_stack[0] .= array_shift($text_stack); } break; } $token_len = strlen($token); if ($tree_char_em) { if ($token_len == 3) { array_shift($token_stack); $span = array_shift($text_stack); $span = $this->runSpanGamut($span); $span = "<strong><em>$span</em></strong>"; $text_stack[0] .= $this->hashPart($span); $em = ''; $strong = ''; } else { $token_stack[0] = str_repeat($token{0}, 3-$token_len); $tag = $token_len == 2 ? "strong" : "em"; $span = $text_stack[0]; $span = $this->runSpanGamut($span); $span = "<$tag>$span</$tag>"; $text_stack[0] = $this->hashPart($span); $$tag = ''; } $tree_char_em = false; } else if ($token_len == 3) { if ($em) { for ($i = 0; $i < 2; ++$i) { $shifted_token = array_shift($token_stack); $tag = strlen($shifted_token) == 2 ? "strong" : "em"; $span = array_shift($text_stack); $span = $this->runSpanGamut($span); $span = "<$tag>$span</$tag>"; $text_stack[0] .= $this->hashPart($span); $$tag = ''; } } else { $em = $token{0}; $strong = "$em$em"; array_unshift($token_stack, $token); array_unshift($text_stack, ''); $tree_char_em = true; } } else if ($token_len == 2) { if ($strong) { if (strlen($token_stack[0]) == 1) { $text_stack[1] .= array_shift($token_stack); $text_stack[0] .= array_shift($text_stack); } array_shift($token_stack); $span = array_shift($text_stack); $span = $this->runSpanGamut($span); $span = "<strong>$span</strong>"; $text_stack[0] .= $this->hashPart($span); $strong = ''; } else { array_unshift($token_stack, $token); array_unshift($text_stack, ''); $strong = $token; } } else { if ($em) { if (strlen($token_stack[0]) == 1) { array_shift($token_stack); $span = array_shift($text_stack); $span = $this->runSpanGamut($span); $span = "<em>$span</em>"; $text_stack[0] .= $this->hashPart($span); $em = ''; } else { $text_stack[0] .= $token; } } else { array_unshift($token_stack, $token); array_unshift($text_stack, ''); $em = $token; } } } return $text_stack[0]; } function doBlockQuotes($text) { $text = preg_replace_callback('/
			  (								# Wrap whole match in $1
				(?>
				  ^[ ]*>[ ]?			# ">" at the start of a line
					.+\n					# rest of the first line
				  (.+\n)*					# subsequent consecutive lines
				  \n*						# blanks
				)+
			  )
			/xm', array(&$this, '_doBlockQuotes_callback'), $text); return $text; } function _doBlockQuotes_callback($matches) { $bq = $matches[1]; $bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq); $bq = $this->runBlockGamut($bq); $bq = preg_replace('/^/m', "  ", $bq); $bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array(&$this, '_doBlockQuotes_callback2'), $bq); return "\n". $this->hashBlock("<blockquote>\n$bq\n</blockquote>")."\n\n"; } function _doBlockQuotes_callback2($matches) { $pre = $matches[1]; $pre = preg_replace('/^  /m', '', $pre); return $pre; } function formParagraphs($text) { $text = preg_replace('/\A\n+|\n+\z/', '', $text); $grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY); foreach ($grafs as $key => $value) { if (!preg_match('/^B\x1A[0-9]+B$/', $value)) { $value = $this->runSpanGamut($value); $value = preg_replace('/^([ ]*)/', "<p>", $value); $value .= "</p>"; $grafs[$key] = $this->unhash($value); } else { $graf = $value; $block = $this->html_hashes[$graf]; $graf = $block; $grafs[$key] = $graf; } } return implode("\n\n", $grafs); } function encodeAttribute($text) { $text = $this->encodeAmpsAndAngles($text); $text = str_replace('"', '&quot;', $text); return $text; } function encodeAmpsAndAngles($text) { if ($this->no_entities) { $text = str_replace('&', '&amp;', $text); } else { $text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', '&amp;', $text);; } $text = str_replace('<', '&lt;', $text); return $text; } function doAutoLinks($text) { $text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}i', array(&$this, '_doAutoLinks_url_callback'), $text); $text = preg_replace_callback('{
			<
			(?:mailto:)?
			(
				(?:
					[-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
				|
					".*?"
				)
				\@
				(?:
					[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
				|
					\[[\d.a-fA-F:]+\]	# IPv4 & IPv6
				)
			)
			>
			}xi', array(&$this, '_doAutoLinks_email_callback'), $text); $text = preg_replace_callback('{<(tel:([^\'">\s]+))>}i',array(&$this, '_doAutoLinks_tel_callback'), $text); return $text; } function _doAutoLinks_tel_callback($matches) { $url = $this->encodeAttribute($matches[1]); $tel = $this->encodeAttribute($matches[2]); $link = "<a href=\"$url\">$tel</a>"; return $this->hashPart($link); } function _doAutoLinks_url_callback($matches) { $url = $this->encodeAttribute($matches[1]); $link = "<a href=\"$url\">$url</a>"; return $this->hashPart($link); } function _doAutoLinks_email_callback($matches) { $address = $matches[1]; $link = $this->encodeEmailAddress($address); return $this->hashPart($link); } function encodeEmailAddress($addr) { $addr = "mailto:" . $addr; $chars = preg_split('/(?<!^)(?!$)/', $addr); $seed = (int)abs(crc32($addr) / strlen($addr)); foreach ($chars as $key => $char) { $ord = ord($char); if ($ord < 128) { $r = ($seed * (1 + $key)) % 100; if ($r > 90 && $char != '@') ; else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';'; else $chars[$key] = '&#'.$ord.';'; } } $addr = implode('', $chars); $text = implode('', array_slice($chars, 7)); $addr = "<a href=\"$addr\">$text</a>"; return $addr; } function parseSpan($str) { $output = ''; $span_re = '{
				(
					\\\\'.$this->escape_chars_re.'
				|
					(?<![`\\\\])
					`+						# code span marker
			'.( $this->no_markup ? '' : '
				|
					<!--    .*?     -->		# comment
				|
					<\?.*?\?> | <%.*?%>		# processing instruction
				|
					<[!$]?[-a-zA-Z0-9:_]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
				|
					<[-a-zA-Z0-9:_]+\s*/> # xml-style empty tag
				|
					</[-a-zA-Z0-9:_]+\s*> # closing tag
			').'
				)
				}xs'; while (1) { $parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE); if ($parts[0] != "") { $output .= $parts[0]; } if (isset($parts[1])) { $output .= $this->handleSpanToken($parts[1], $parts[2]); $str = $parts[2]; } else { break; } } return $output; } function handleSpanToken($token, &$str) { switch ($token{0}) { case "\\": return $this->hashPart("&#". ord($token{1}). ";"); case "`": if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm', $str, $matches)) { $str = $matches[2]; $codespan = $this->makeCodeSpan($matches[1]); return $this->hashPart($codespan); } return $token; default: return $this->hashPart($token); } } function outdent($text) { return preg_replace('/^(\t|[ ]{1,'.$this->tab_width.'})/m', '', $text); } var $utf8_strlen = 'mb_strlen'; function detab($text) { $text = preg_replace_callback('/^.*\t.*$/m', array(&$this, '_detab_callback'), $text); return $text; } function _detab_callback($matches) { $line = $matches[0]; $strlen = $this->utf8_strlen; $blocks = explode("\t", $line); $line = $blocks[0]; unset($blocks[0]); foreach ($blocks as $block) { $amount = $this->tab_width - $strlen($line, 'UTF-8') % $this->tab_width; $line .= str_repeat(" ", $amount) . $block; } return $line; } function _initDetab() { if (function_exists($this->utf8_strlen)) return; $this->utf8_strlen = create_function('$text', 'return preg_match_all(
			"/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/", 
			$text, $m);'); } function unhash($text) { return preg_replace_callback('/(.)\x1A[0-9]+\1/', array(&$this, '_unhash_callback'), $text); } function _unhash_callback($matches) { return $this->html_hashes[$matches[0]]; } } class MarkdownExtra_Parser extends Markdown_Parser { var $fn_id_prefix = ""; var $fn_link_title = MARKDOWN_FN_LINK_TITLE; var $fn_backlink_title = MARKDOWN_FN_BACKLINK_TITLE; var $fn_link_class = MARKDOWN_FN_LINK_CLASS; var $fn_backlink_class = MARKDOWN_FN_BACKLINK_CLASS; var $code_class_prefix = MARKDOWN_CODE_CLASS_PREFIX; var $code_attr_on_pre = MARKDOWN_CODE_ATTR_ON_PRE; var $predef_abbr = array(); function MarkdownExtra_Parser() { $this->escape_chars .= ':|'; $this->document_gamut += array( "doFencedCodeBlocks" => 5, "stripFootnotes" => 15, "stripAbbreviations" => 25, "appendFootnotes" => 50, ); $this->block_gamut += array( "doFencedCodeBlocks" => 5, "doTables" => 15, "doDefLists" => 45, ); $this->span_gamut += array( "doFootnotes" => 5, "doAbbreviations" => 70, ); parent::Markdown_Parser(); } var $footnotes = array(); var $footnotes_ordered = array(); var $footnotes_ref_count = array(); var $footnotes_numbers = array(); var $abbr_desciptions = array(); var $abbr_word_re = ''; var $footnote_counter = 1; function setup() { parent::setup(); $this->footnotes = array(); $this->footnotes_ordered = array(); $this->footnotes_ref_count = array(); $this->footnotes_numbers = array(); $this->abbr_desciptions = array(); $this->abbr_word_re = ''; $this->footnote_counter = 1; foreach ($this->predef_abbr as $abbr_word => $abbr_desc) { if ($this->abbr_word_re) $this->abbr_word_re .= '|'; $this->abbr_word_re .= preg_quote($abbr_word); $this->abbr_desciptions[$abbr_word] = trim($abbr_desc); } } function teardown() { $this->footnotes = array(); $this->footnotes_ordered = array(); $this->footnotes_ref_count = array(); $this->footnotes_numbers = array(); $this->abbr_desciptions = array(); $this->abbr_word_re = ''; parent::teardown(); } var $id_class_attr_catch_re = '\{((?:[ ]*[#.][-_:a-zA-Z0-9]+){1,})[ ]*\}'; var $id_class_attr_nocatch_re = '\{(?:[ ]*[#.][-_:a-zA-Z0-9]+){1,}[ ]*\}'; function doExtraAttributes($tag_name, $attr) { if (empty($attr)) return ""; preg_match_all('/[#.][-_:a-zA-Z0-9]+/', $attr, $matches); $elements = $matches[0]; $classes = array(); $id = false; foreach ($elements as $element) { if ($element{0} == '.') { $classes[] = substr($element, 1); } else if ($element{0} == '#') { if ($id === false) $id = substr($element, 1); } } $attr_str = ""; if (!empty($id)) { $attr_str .= ' id="'.$id.'"'; } if (!empty($classes)) { $attr_str .= ' class="'.implode(" ", $classes).'"'; } return $attr_str; } function stripLinkDefinitions($text) { $less_than_tab = $this->tab_width - 1; $text = preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>			# url = $2
							|
							  (\S+?)			# url = $3
							)
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)			# lookbehind for whitespace
								["(]
								(.*?)			# title = $4
								[")]
								[ ]*
							)?	# title is optional
					(?:[ ]* '.$this->id_class_attr_catch_re.' )?  # $5 = extra id & class attr
							(?:\n+|\Z)
			}xm', array(&$this, '_stripLinkDefinitions_callback'), $text); return $text; } function _stripLinkDefinitions_callback($matches) { $link_id = strtolower($matches[1]); $url = $matches[2] == '' ? $matches[3] : $matches[2]; $this->urls[$link_id] = $url; $this->titles[$link_id] =& $matches[4]; $this->ref_attr[$link_id] = $this->doExtraAttributes("", $dummy =& $matches[5]); return ''; } var $block_tags_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend|article|section|nav|aside|hgroup|header|footer|figcaption'; var $context_block_tags_re = 'script|noscript|ins|del|iframe|object|source|track|param|math|svg|canvas|audio|video'; var $contain_span_tags_re = 'p|h[1-6]|li|dd|dt|td|th|legend|address'; var $clean_tags_re = 'script|math|svg'; var $auto_close_tags_re = 'hr|img|param|source|track'; function hashHTMLBlocks($text) { if ($this->no_markup) return $text; list($text, ) = $this->_hashHTMLBlocks_inMarkdown($text); return $text; } function _hashHTMLBlocks_inMarkdown($text, $indent = 0, $enclosing_tag_re = '', $span = false) { if ($text === '') return array('', ''); $newline_before_re = '/(?:^\n?|\n\n)*$/'; $newline_after_re = '{
				^						# Start of text following the tag.
				(?>[ ]*<!--.*?-->)?		# Optional comment.
				[ ]*\n					# Must be followed by newline.
			}xs'; $block_tag_re = '{
				(					# $2: Capture whole tag.
					</?					# Any opening or closing tag.
						(?>				# Tag name.
							'.$this->block_tags_re.'			|
							'.$this->context_block_tags_re.'	|
							'.$this->clean_tags_re.'        	|
							(?!\s)'.$enclosing_tag_re.'
						)
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	# Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				# Anything but quotes and `>`.
							)*?
						)?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				'. ( !$span ? ' # If not in span.
				|
					# Indented code block
					(?: ^[ ]*\n | ^ | \n[ ]*\n )
					[ ]{'.($indent+4).'}[^\n]* \n
					(?>
						(?: [ ]{'.($indent+4).'}[^\n]* | [ ]* ) \n
					)*
				|
					# Fenced code block marker
					(?<= ^ | \n )
					[ ]{0,'.($indent+3).'}(?:~{3,}|`{3,})
									[ ]*
					(?:
					\.?[-_:a-zA-Z0-9]+ # standalone class name
					|
						'.$this->id_class_attr_nocatch_re.' # extra attributes
					)?
					[ ]*
					(?= \n )
				' : '' ). ' # End (if not is span).
				|
					# Code span marker
					# Note, this regex needs to go after backtick fenced
					# code blocks but it should also be kept outside of the
					# "if not in span" condition adding backticks to the parser
					`+
				)
			}xs'; $depth = 0; $parsed = ""; do { $parts = preg_split($block_tag_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE); if ($span) { $void = $this->hashPart("", ':'); $newline = "$void\n"; $parts[0] = $void . str_replace("\n", $newline, $parts[0]) . $void; } $parsed .= $parts[0]; if (count($parts) < 3) { $text = ""; break; } $tag = $parts[1]; $text = $parts[2]; $tag_re = preg_quote($tag); if (preg_match('{^\n?([ ]{0,'.($indent+3).'})(~{3,}|`{3,})[ ]*(?:\.?[-_:a-zA-Z0-9]+|'.$this->id_class_attr_nocatch_re.')?[ ]*\n?$}', $tag, $capture)) { $fence_indent = strlen($capture[1]); $fence_re = $capture[2]; if (preg_match('{^(?>.*\n)*?[ ]{'.($fence_indent).'}'.$fence_re.'[ ]*(?:\n|$)}', $text, $matches)) { $parsed .= $tag . $matches[0]; $text = substr($text, strlen($matches[0])); } else { $parsed .= $tag; } } else if ($tag{0} == "\n" || $tag{0} == " ") { $parsed .= $tag; } else if ($tag{0} == "`") { $tag_re = preg_quote($tag); if (preg_match('{^(?>.+?|\n(?!\n))*?(?<!`)'.$tag_re.'(?!`)}', $text, $matches)) { $parsed .= $tag . $matches[0]; $text = substr($text, strlen($matches[0])); } else { $parsed .= $tag; } } else if (preg_match('{^<(?:'.$this->block_tags_re.')\b}', $tag) || ( preg_match('{^<(?:'.$this->context_block_tags_re.')\b}', $tag) && preg_match($newline_before_re, $parsed) && preg_match($newline_after_re, $text) ) ) { list($block_text, $text) = $this->_hashHTMLBlocks_inHTML($tag . $text, "hashBlock", true); $parsed .= "\n\n$block_text\n\n"; } else if (preg_match('{^<(?:'.$this->clean_tags_re.')\b}', $tag) || $tag{1} == '!' || $tag{1} == '?') { list($block_text, $text) = $this->_hashHTMLBlocks_inHTML($tag . $text, "hashClean", false); $parsed .= $block_text; } else if ($enclosing_tag_re !== '' && preg_match('{^</?(?:'.$enclosing_tag_re.')\b}', $tag)) { if ($tag{1} == '/') $depth--; else if ($tag{strlen($tag)-2} != '/') $depth++; if ($depth < 0) { $text = $tag . $text; break; } $parsed .= $tag; } else { $parsed .= $tag; } } while ($depth >= 0); return array($parsed, $text); } function _hashHTMLBlocks_inHTML($text, $hash_method, $md_attr) { if ($text === '') return array('', ''); $markdown_attr_re = '
			{
				\s*			# Eat whitespace before the `markdown` attribute
				markdown
				\s*=\s*
				(?>
					(["\'])		# $1: quote delimiter		
					(.*?)		# $2: attribute value
					\1			# matching delimiter	
				|
					([^\s>]*)	# $3: unquoted attribute value
				)
				()				# $4: make $3 always defined (avoid warnings)
			}xs'; $tag_re = '{
				(					# $2: Capture whole tag.
					</?					# Any opening or closing tag.
						[\w:$]+			# Tag name.
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	# Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				# Anything but quotes and `>`.
							)*?
						)?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				)
			}xs'; $original_text = $text; $depth = 0; $block_text = ""; $parsed = ""; if (preg_match('/^<([\w:$]*)\b/', $text, $matches)) $base_tag_name_re = $matches[1]; do { $parts = preg_split($tag_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE); if (count($parts) < 3) { return array($original_text{0}, substr($original_text, 1)); } $block_text .= $parts[0]; $tag = $parts[1]; $text = $parts[2]; if (preg_match('{^</?(?:'.$this->auto_close_tags_re.')\b}', $tag) || $tag{1} == '!' || $tag{1} == '?') { $block_text .= $tag; } else { if (preg_match('{^</?'.$base_tag_name_re.'\b}', $tag)) { if ($tag{1} == '/') $depth--; else if ($tag{strlen($tag)-2} != '/') $depth++; } if ($md_attr && preg_match($markdown_attr_re, $tag, $attr_m) && preg_match('/^1|block|span$/', $attr_m[2] . $attr_m[3])) { $tag = preg_replace($markdown_attr_re, '', $tag); $this->mode = $attr_m[2] . $attr_m[3]; $span_mode = $this->mode == 'span' || $this->mode != 'block' && preg_match('{^<(?:'.$this->contain_span_tags_re.')\b}', $tag); if (preg_match('/(?:^|\n)( *?)(?! ).*?$/', $block_text, $matches)) { $strlen = $this->utf8_strlen; $indent = $strlen($matches[1], 'UTF-8'); } else { $indent = 0; } $block_text .= $tag; $parsed .= $this->$hash_method($block_text); preg_match('/^<([\w:$]*)\b/', $tag, $matches); $tag_name_re = $matches[1]; list ($block_text, $text) = $this->_hashHTMLBlocks_inMarkdown($text, $indent, $tag_name_re, $span_mode); if ($indent > 0) { $block_text = preg_replace("/^[ ]{1,$indent}/m", "", $block_text); } if (!$span_mode) $parsed .= "\n\n$block_text\n\n"; else $parsed .= "$block_text"; $block_text = ""; } else $block_text .= $tag; } } while ($depth > 0); $parsed .= $this->$hash_method($block_text); return array($parsed, $text); } function hashClean($text) { return $this->hashPart($text, 'C'); } function doAnchors($text) { if ($this->in_anchor) return $text; $this->in_anchor = true; $text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]
			)
			}xs', array(&$this, '_doAnchors_reference_callback'), $text); $text = preg_replace_callback('{
			(				# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]
			  \(			# literal paren
				[ \n]*
				(?:
					<(.+?)>	# href = $3
				|
					('.$this->nested_url_parenthesis_re.')	# href = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# Title = $7
				  \6		# matching quote
				  [ \n]*	# ignore any spaces/tabs between closing quote and )
				)?			# title is optional
			  \)
			  (?:[ ]? '.$this->id_class_attr_catch_re.' )?	 # $8 = id/class attributes
			)
			}xs', array(&$this, '_doAnchors_inline_callback'), $text); $text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs', array(&$this, '_doAnchors_reference_callback'), $text); $this->in_anchor = false; return $text; } function _doAnchors_reference_callback($matches) { $whole_match = $matches[1]; $link_text = $matches[2]; $link_id =& $matches[3]; if ($link_id == "") { $link_id = $link_text; } $link_id = strtolower($link_id); $link_id = preg_replace('{[ ]?\n}', ' ', $link_id); if (isset($this->urls[$link_id])) { $url = $this->urls[$link_id]; $url = $this->encodeAttribute($url); $result = "<a href=\"$url\""; if ( isset( $this->titles[$link_id] ) ) { $title = $this->titles[$link_id]; $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } if (isset($this->ref_attr[$link_id])) $result .= $this->ref_attr[$link_id]; $link_text = $this->runSpanGamut($link_text); $result .= ">$link_text</a>"; $result = $this->hashPart($result); } else { $result = $whole_match; } return $result; } function _doAnchors_inline_callback($matches) { $whole_match = $matches[1]; $link_text = $this->runSpanGamut($matches[2]); $url = $matches[3] == '' ? $matches[4] : $matches[3]; $title =& $matches[7]; $attr = $this->doExtraAttributes("a", $dummy =& $matches[8]); $url = $this->encodeAttribute($url); $result = "<a href=\"$url\""; if (isset($title)) { $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $result .= $attr; $link_text = $this->runSpanGamut($link_text); $result .= ">$link_text</a>"; return $this->hashPart($result); } function doImages($text) { $text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]

			)
			}xs', array(&$this, '_doImages_reference_callback'), $text); $text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->nested_url_parenthesis_re.')	# src url = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# title = $7
				  \6		# matching quote
				  [ \n]*
				)?			# title is optional
			  \)
			  (?:[ ]? '.$this->id_class_attr_catch_re.' )?	 # $8 = id/class attributes
			)
			}xs', array(&$this, '_doImages_inline_callback'), $text); return $text; } function _doImages_reference_callback($matches) { $whole_match = $matches[1]; $alt_text = $matches[2]; $link_id = strtolower($matches[3]); if ($link_id == "") { $link_id = strtolower($alt_text); } $alt_text = $this->encodeAttribute($alt_text); if (isset($this->urls[$link_id])) { $url = $this->encodeAttribute($this->urls[$link_id]); $result = "<img src=\"$url\" alt=\"$alt_text\""; if (isset($this->titles[$link_id])) { $title = $this->titles[$link_id]; $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } if (isset($this->ref_attr[$link_id])) $result .= $this->ref_attr[$link_id]; $result .= $this->empty_element_suffix; $result = $this->hashPart($result); } else { $result = $whole_match; } return $result; } function _doImages_inline_callback($matches) { $whole_match = $matches[1]; $alt_text = $matches[2]; $url = $matches[3] == '' ? $matches[4] : $matches[3]; $title =& $matches[7]; $attr = $this->doExtraAttributes("img", $dummy =& $matches[8]); $alt_text = $this->encodeAttribute($alt_text); $url = $this->encodeAttribute($url); $result = "<img src=\"$url\" alt=\"$alt_text\""; if (isset($title)) { $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $result .= $attr; $result .= $this->empty_element_suffix; return $this->hashPart($result); } function doHeaders($text) { $text = preg_replace_callback( '{
				(^.+?)								# $1: Header text
				(?:[ ]+ '.$this->id_class_attr_catch_re.' )?	 # $3 = id/class attributes
				[ ]*\n(=+|-+)[ ]*\n+				# $3: Header footer
			}mx', array(&$this, '_doHeaders_callback_setext'), $text); $text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				(?:[ ]+ '.$this->id_class_attr_catch_re.' )?	 # $3 = id/class attributes
				[ ]*
				\n+
			}xm', array(&$this, '_doHeaders_callback_atx'), $text); return $text; } function _doHeaders_callback_setext($matches) { if ($matches[3] == '-' && preg_match('{^- }', $matches[1])) return $matches[0]; $level = $matches[3]{0} == '=' ? 1 : 2; $attr = $this->doExtraAttributes("h$level", $dummy =& $matches[2]); $block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>"; return "\n" . $this->hashBlock($block) . "\n\n"; } function _doHeaders_callback_atx($matches) { $level = strlen($matches[1]); $attr = $this->doExtraAttributes("h$level", $dummy =& $matches[3]); $block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>"; return "\n" . $this->hashBlock($block) . "\n\n"; } function doTables($text) { $less_than_tab = $this->tab_width - 1; $text = preg_replace_callback('
			{
				^							# Start of a line
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				[|]							# Optional leading pipe (present)
				(.+) \n						# $1: Header row (at least one pipe)
				
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				[|] ([ ]*[-:]+[-| :]*) \n	# $2: Header underline
				
				(							# $3: Cells
					(?>
						[ ]*				# Allowed whitespace.
						[|] .* \n			# Row content.
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm', array(&$this, '_doTable_leadingPipe_callback'), $text); $text = preg_replace_callback('
			{
				^							# Start of a line
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				(\S.*[|].*) \n				# $1: Header row (at least one pipe)
				
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				([-:]+[ ]*[|][-| :]*) \n	# $2: Header underline
				
				(							# $3: Cells
					(?>
						.* [|] .* \n		# Row content
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm', array(&$this, '_DoTable_callback'), $text); return $text; } function _doTable_leadingPipe_callback($matches) { $head = $matches[1]; $underline = $matches[2]; $content = $matches[3]; $content = preg_replace('/^ *[|]/m', '', $content); return $this->_doTable_callback(array($matches[0], $head, $underline, $content)); } function _doTable_callback($matches) { $head = $matches[1]; $underline = $matches[2]; $content = $matches[3]; $head = preg_replace('/[|] *$/m', '', $head); $underline = preg_replace('/[|] *$/m', '', $underline); $content = preg_replace('/[|] *$/m', '', $content); $separators = preg_split('/ *[|] */', $underline); foreach ($separators as $n => $s) { if (preg_match('/^ *-+: *$/', $s)) $attr[$n] = ' align="right"'; else if (preg_match('/^ *:-+: *$/', $s))$attr[$n] = ' align="center"'; else if (preg_match('/^ *:-+ *$/', $s)) $attr[$n] = ' align="left"'; else $attr[$n] = ''; } $head = $this->parseSpan($head); $headers = preg_split('/ *[|] */', $head); $col_count = count($headers); $attr = array_pad($attr, $col_count, ''); $text = "<table>\n"; $text .= "<thead>\n"; $text .= "<tr>\n"; foreach ($headers as $n => $header) $text .= "  <th$attr[$n]>".$this->runSpanGamut(trim($header))."</th>\n"; $text .= "</tr>\n"; $text .= "</thead>\n"; $rows = explode("\n", trim($content, "\n")); $text .= "<tbody>\n"; foreach ($rows as $row) { $row = $this->parseSpan($row); $row_cells = preg_split('/ *[|] */', $row, $col_count); $row_cells = array_pad($row_cells, $col_count, ''); $text .= "<tr>\n"; foreach ($row_cells as $n => $cell) $text .= "  <td$attr[$n]>".$this->runSpanGamut(trim($cell))."</td>\n"; $text .= "</tr>\n"; } $text .= "</tbody>\n"; $text .= "</table>"; return $this->hashBlock($text) . "\n"; } function doDefLists($text) { $less_than_tab = $this->tab_width - 1; $whole_list_re = '(?>
			(								# $1 = whole list
			  (								# $2
				[ ]{0,'.$less_than_tab.'}
				((?>.*\S.*\n)+)				# $3 = defined term
				\n?
				[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
			  )
			  (?s:.+?)
			  (								# $4
				  \z
				|
				  \n{2,}
				  (?=\S)
				  (?!						# Negative lookahead for another term
					[ ]{0,'.$less_than_tab.'}
					(?: \S.*\n )+?			# defined term
					\n?
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
				  (?!						# Negative lookahead for another definition
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
			  )
			)
		)'; $text = preg_replace_callback('{
				(?>\A\n?|(?<=\n\n))
				'.$whole_list_re.'
			}mx', array(&$this, '_doDefLists_callback'), $text); return $text; } function _doDefLists_callback($matches) { $list = $matches[1]; $result = trim($this->processDefListItems($list)); $result = "<dl>\n" . $result . "\n</dl>"; return $this->hashBlock($result) . "\n\n"; } function processDefListItems($list_str) { $less_than_tab = $this->tab_width - 1; $list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str); $list_str = preg_replace_callback('{
			(?>\A\n?|\n\n+)					# leading line
			(								# definition terms = $1
				[ ]{0,'.$less_than_tab.'}	# leading whitespace
				(?!\:[ ]|[ ])				# negative lookahead for a definition
											#   mark (colon) or more whitespace.
				(?> \S.* \n)+?				# actual term (not whitespace).	
			)			
			(?=\n?[ ]{0,3}:[ ])				# lookahead for following line feed 
											#   with a definition mark.
			}xm', array(&$this, '_processDefListItems_callback_dt'), $list_str); $list_str = preg_replace_callback('{
			\n(\n+)?						# leading line = $1
			(								# marker space = $2
				[ ]{0,'.$less_than_tab.'}	# whitespace before colon
				\:[ ]+						# definition mark (colon)
			)
			((?s:.+?))						# definition text = $3
			(?= \n+ 						# stop at next definition mark,
				(?:							# next term or end of text
					[ ]{0,'.$less_than_tab.'} \:[ ]	|
					<dt> | \z
				)						
			)					
			}xm', array(&$this, '_processDefListItems_callback_dd'), $list_str); return $list_str; } function _processDefListItems_callback_dt($matches) { $terms = explode("\n", trim($matches[1])); $text = ''; foreach ($terms as $term) { $term = $this->runSpanGamut(trim($term)); $text .= "\n<dt>" . $term . "</dt>"; } return $text . "\n"; } function _processDefListItems_callback_dd($matches) { $leading_line = $matches[1]; $marker_space = $matches[2]; $def = $matches[3]; if ($leading_line || preg_match('/\n{2,}/', $def)) { $def = str_repeat(' ', strlen($marker_space)) . $def; $def = $this->runBlockGamut($this->outdent($def . "\n\n")); $def = "\n". $def ."\n"; } else { $def = rtrim($def); $def = $this->runSpanGamut($this->outdent($def)); } return "\n<dd>" . $def . "</dd>\n"; } function doFencedCodeBlocks($text) { $less_than_tab = $this->tab_width; $text = preg_replace_callback('{
				(?:\n|\A)
				# 1: Opening marker
				(
					(?:~{3,}|`{3,}) # 3 or more tildes/backticks.
				)
				[ ]*
				(?:
					\.?([-_:a-zA-Z0-9]+) # 2: standalone class name
				|
					'.$this->id_class_attr_catch_re.' # 3: Extra attributes
				)?
				[ ]* \n # Whitespace and newline following marker.
				
				# 4: Content
				(
					(?>
						(?!\1 [ ]* \n)	# Not a closing marker.
						.*\n+
					)+
				)
				
				# Closing marker.
				\1 [ ]* (?= \n )
			}xm', array(&$this, '_doFencedCodeBlocks_callback'), $text); return $text; } function _doFencedCodeBlocks_callback($matches) { $classname =& $matches[2]; $attrs =& $matches[3]; $codeblock = $matches[4]; $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES); $codeblock = preg_replace_callback('/^\n+/', array(&$this, '_doFencedCodeBlocks_newlines'), $codeblock); if ($classname != "") { if ($classname{0} == '.') $classname = substr($classname, 1); $attr_str = ' class="'.$this->code_class_prefix.$classname.'"'; } else { $attr_str = $this->doExtraAttributes($this->code_attr_on_pre ? "pre" : "code", $attrs); } $pre_attr_str = $this->code_attr_on_pre ? $attr_str : ''; $code_attr_str = $this->code_attr_on_pre ? '' : $attr_str; $codeblock = "<pre$pre_attr_str><code$code_attr_str>$codeblock</code></pre>"; return "\n\n".$this->hashBlock($codeblock)."\n\n"; } function _doFencedCodeBlocks_newlines($matches) { return str_repeat("<br$this->empty_element_suffix", strlen($matches[0])); } var $em_relist = array( '' => '(?:(?<!\*)\*(?!\*)|(?<![a-zA-Z0-9_])_(?!_))(?=\S|$)(?![\.,:;]\s)', '*' => '(?<=\S|^)(?<!\*)\*(?!\*)', '_' => '(?<=\S|^)(?<!_)_(?![a-zA-Z0-9_])', ); var $strong_relist = array( '' => '(?:(?<!\*)\*\*(?!\*)|(?<![a-zA-Z0-9_])__(?!_))(?=\S|$)(?![\.,:;]\s)', '**' => '(?<=\S|^)(?<!\*)\*\*(?!\*)', '__' => '(?<=\S|^)(?<!_)__(?![a-zA-Z0-9_])', ); var $em_strong_relist = array( '' => '(?:(?<!\*)\*\*\*(?!\*)|(?<![a-zA-Z0-9_])___(?!_))(?=\S|$)(?![\.,:;]\s)', '***' => '(?<=\S|^)(?<!\*)\*\*\*(?!\*)', '___' => '(?<=\S|^)(?<!_)___(?![a-zA-Z0-9_])', ); function formParagraphs($text) { $text = preg_replace('/\A\n+|\n+\z/', '', $text); $grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY); foreach ($grafs as $key => $value) { $value = trim($this->runSpanGamut($value)); $is_p = !preg_match('/^B\x1A[0-9]+B|^C\x1A[0-9]+C$/', $value); if ($is_p) { $value = "<p>$value</p>"; } $grafs[$key] = $value; } $text = implode("\n\n", $grafs); $text = $this->unhash($text); return $text; } function stripFootnotes($text) { $less_than_tab = $this->tab_width - 1; $text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\[\^(.+?)\][ ]?:	# note_id = $1
			  [ ]*
			  \n?					# maybe *one* newline
			(						# text = $2 (no blank lines allowed)
				(?:					
					.+				# actual text
				|
					\n				# newlines but 
					(?!\[\^.+?\]:\s)# negative lookahead for footnote marker.
					(?!\n+[ ]{0,3}\S)# ensure line is not blank and followed 
									# by non-indented content
				)*
			)		
			}xm', array(&$this, '_stripFootnotes_callback'), $text); return $text; } function _stripFootnotes_callback($matches) { $note_id = $this->fn_id_prefix . $matches[1]; $this->footnotes[$note_id] = $this->outdent($matches[2]); return ''; } function doFootnotes($text) { if (!$this->in_anchor) { $text = preg_replace('{\[\^(.+?)\]}', "F\x1Afn:\\1\x1A:", $text); } return $text; } function appendFootnotes($text) { $text = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', array(&$this, '_appendFootnotes_callback'), $text); if (!empty($this->footnotes_ordered)) { $text .= "\n\n"; $text .= "<div class=\"footnotes\">\n"; $text .= "<hr". $this->empty_element_suffix ."\n"; $text .= "<ol>\n\n"; $attr = " rev=\"footnote\""; if ($this->fn_backlink_class != "") { $class = $this->fn_backlink_class; $class = $this->encodeAttribute($class); $attr .= " class=\"$class\""; } if ($this->fn_backlink_title != "") { $title = $this->fn_backlink_title; $title = $this->encodeAttribute($title); $attr .= " title=\"$title\""; } $num = 0; while (!empty($this->footnotes_ordered)) { $footnote = reset($this->footnotes_ordered); $note_id = key($this->footnotes_ordered); unset($this->footnotes_ordered[$note_id]); $ref_count = $this->footnotes_ref_count[$note_id]; unset($this->footnotes_ref_count[$note_id]); unset($this->footnotes[$note_id]); $footnote .= "\n"; $footnote = $this->runBlockGamut("$footnote\n"); $footnote = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', array(&$this, '_appendFootnotes_callback'), $footnote); $attr = str_replace("%%", ++$num, $attr); $note_id = $this->encodeAttribute($note_id); $backlink = "<a href=\"#fnref:$note_id\"$attr>&#8617;</a>"; for ($ref_num = 2; $ref_num <= $ref_count; ++$ref_num) { $backlink .= " <a href=\"#fnref$ref_num:$note_id\"$attr>&#8617;</a>"; } if (preg_match('{</p>$}', $footnote)) { $footnote = substr($footnote, 0, -4) . "&#160;$backlink</p>"; } else { $footnote .= "\n\n<p>$backlink</p>"; } $text .= "<li id=\"fn:$note_id\">\n"; $text .= $footnote . "\n"; $text .= "</li>\n\n"; } $text .= "</ol>\n"; $text .= "</div>"; } return $text; } function _appendFootnotes_callback($matches) { $node_id = $this->fn_id_prefix . $matches[1]; if (isset($this->footnotes[$node_id])) { $num =& $this->footnotes_numbers[$node_id]; if (!isset($num)) { $this->footnotes_ordered[$node_id] = $this->footnotes[$node_id]; $this->footnotes_ref_count[$node_id] = 1; $num = $this->footnote_counter++; $ref_count_mark = ''; } else { $ref_count_mark = $this->footnotes_ref_count[$node_id] += 1; } $attr = " rel=\"footnote\""; if ($this->fn_link_class != "") { $class = $this->fn_link_class; $class = $this->encodeAttribute($class); $attr .= " class=\"$class\""; } if ($this->fn_link_title != "") { $title = $this->fn_link_title; $title = $this->encodeAttribute($title); $attr .= " title=\"$title\""; } $attr = str_replace("%%", $num, $attr); $node_id = $this->encodeAttribute($node_id); return "<sup id=\"fnref$ref_count_mark:$node_id\">". "<a href=\"#fn:$node_id\"$attr>$num</a>". "</sup>"; } return "[^".$matches[1]."]"; } function stripAbbreviations($text) { $less_than_tab = $this->tab_width - 1; $text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\*\[(.+?)\][ ]?:	# abbr_id = $1
			(.*)					# text = $2 (no blank lines allowed)	
			}xm', array(&$this, '_stripAbbreviations_callback'), $text); return $text; } function _stripAbbreviations_callback($matches) { $abbr_word = $matches[1]; $abbr_desc = $matches[2]; if ($this->abbr_word_re) $this->abbr_word_re .= '|'; $this->abbr_word_re .= preg_quote($abbr_word); $this->abbr_desciptions[$abbr_word] = trim($abbr_desc); return ''; } function doAbbreviations($text) { if ($this->abbr_word_re) { $text = preg_replace_callback('{'. '(?<![\w\x1A])'. '(?:'.$this->abbr_word_re.')'. '(?![\w\x1A])'. '}', array(&$this, '_doAbbreviations_callback'), $text); } return $text; } function _doAbbreviations_callback($matches) { $abbr = $matches[0]; if (isset($this->abbr_desciptions[$abbr])) { $desc = $this->abbr_desciptions[$abbr]; if (empty($desc)) { return $this->hashPart("<abbr>$abbr</abbr>"); } else { $desc = $this->encodeAttribute($desc); return $this->hashPart("<abbr title=\"$desc\">$abbr</abbr>"); } } else { return $matches[0]; } } } ?>
