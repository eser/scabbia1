<?php

	/*

	PHP Markdown Extra
	==================

	Description
	-----------

	This is a PHP port of the original Markdown formatter written in Perl
	by John Gruber. This special "Extra" version of PHP Markdown features
	further enhancements to the syntax for making additional constructs
	such as tables and definition list.

	Markdown is a text-to-HTML filter; it translates an easy-to-read /
	easy-to-write structured text format into HTML. Markdown's text format
	is most similar to that of plain text email, and supports features such
	as headers, *emphasis*, code blocks, blockquotes, and links.

	Markdown's syntax is designed not as a generic markup language, but
	specifically to serve as a front-end to (X)HTML. You can use span-level
	HTML tags anywhere in a Markdown document, and you can use block level
	HTML tags (like <div> and <table> as well).

	For more information about Markdown's syntax, see:

	<http://daringfireball.net/projects/markdown/>


	Bugs
	----

	To file bug reports please send email to:

	<michel.fortin@michelf.com>

	Please include with your report: (1) the example input; (2) the output you
	expected; (3) the output Markdown actually produced.


	Version History
	---------------

	See the readme file for detailed release notes for this version.


	Copyright and License
	---------------------

	PHP Markdown & Extra
	Copyright (c) 2004-2009 Michel Fortin
	<http://michelf.com/>
	All rights reserved.

	Based on Markdown
	Copyright (c) 2003-2006 John Gruber
	<http://daringfireball.net/>
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are
	met:

	*	Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.

	*	Redistributions in binary form must reproduce the above copyright
	notice, this list of conditions and the following disclaimer in the
	documentation and/or other materials provided with the distribution.

	*	Neither the name "Markdown" nor the names of its contributors may
	be used to endorse or promote products derived from this software
	without specific prior written permission.

	This software is provided by the copyright holders and contributors "as
	is" and any express or implied warranties, including, but not limited
	to, the implied warranties of merchantability and fitness for a
	particular purpose are disclaimed. In no event shall the copyright owner
	or contributors be liable for any direct, indirect, incidental, special,
	exemplary, or consequential damages (including, but not limited to,
	procurement of substitute goods or services; loss of use, data, or
	profits; or business interruption) however caused and on any theory of
	liability, whether in contract, strict liability, or tort (including
	negligence or otherwise) arising in any way out of the use of this
	software, even if advised of the possibility of such damage.

	*/

	define('MARKDOWN_VERSION', "1.0.1o"); # Sun 8 Jan 2012
	define('MARKDOWNEXTRA_VERSION', "1.2.5"); # Sun 8 Jan 2012


	#
	# Global default settings:
	#

	# Change to ">" for HTML output
	@define('MARKDOWN_EMPTY_ELEMENT_SUFFIX', " />");

	# Define the width of a tab for code blocks.
	@define('MARKDOWN_TAB_WIDTH', 4);

	# Optional title attribute for footnote links and backlinks.
	@define('MARKDOWN_FN_LINK_TITLE', "");
	@define('MARKDOWN_FN_BACKLINK_TITLE', "");

	# Optional class attribute for footnote links and backlinks.
	@define('MARKDOWN_FN_LINK_CLASS', "");
	@define('MARKDOWN_FN_BACKLINK_CLASS', "");


	#
	# WordPress settings:
	#

	# Change to false to remove Markdown from posts and/or comments.
	@define('MARKDOWN_WP_POSTS', true);
	@define('MARKDOWN_WP_COMMENTS', true);


	### Standard Function Interface ###

	@define('MARKDOWN_PARSER_CLASS', 'MarkdownExtra_Parser');

	function Markdown($text) {
		#
		# Initialize the parser and return the result of its transform method.
		#
		# Setup static parser variable.
		static $parser;
		if(!isset($parser)) {
			$parser_class = MARKDOWN_PARSER_CLASS;
			$parser = new $parser_class;
		}

		# Transform text using parser.
		return $parser->transform($text);
	}


	### WordPress Plugin Interface ###

	/*
	Plugin Name: Markdown Extra
	Plugin URI: http://michelf.com/projects/php-markdown/
	Description: <a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a> allows you to write using an easy-to-read, easy-to-write plain text format. Based on the original Perl version by <a href="http://daringfireball.net/">John Gruber</a>. <a href="http://michelf.com/projects/php-markdown/">More...</a>
	Version: 1.2.5
	Author: Michel Fortin
	Author URI: http://michelf.com/
	*/

	if(isset($wp_version)) {
		# More details about how it works here:
		# <http://michelf.com/weblog/2005/wordpress-text-flow-vs-markdown/>

		# Post content and excerpts
		# - Remove WordPress paragraph generator.
		# - Run Markdown on excerpt, then remove all tags.
		# - Add paragraph tag around the excerpt, but remove it for the excerpt rss.
		if(MARKDOWN_WP_POSTS) {
			remove_filter('the_content', 'wpautop');
			remove_filter('the_content_rss', 'wpautop');
			remove_filter('the_excerpt', 'wpautop');
			add_filter('the_content', 'mdwp_MarkdownPost', 6);
			add_filter('the_content_rss', 'mdwp_MarkdownPost', 6);
			add_filter('get_the_excerpt', 'mdwp_MarkdownPost', 6);
			add_filter('get_the_excerpt', 'trim', 7);
			add_filter('the_excerpt', 'mdwp_add_p');
			add_filter('the_excerpt_rss', 'mdwp_strip_p');

			remove_filter('content_save_pre', 'balanceTags', 50);
			remove_filter('excerpt_save_pre', 'balanceTags', 50);
			add_filter('the_content', 'balanceTags', 50);
			add_filter('get_the_excerpt', 'balanceTags', 9);
		}

		# Add a footnote id prefix to posts when inside a loop.
		function mdwp_MarkdownPost($text) {
			static $parser;
			if(!$parser) {
				$parser_class = MARKDOWN_PARSER_CLASS;
				$parser = new $parser_class;
			}
			if(is_single() || is_page() || is_feed()) {
				$parser->fn_id_prefix = "";
			}
			else {
				$parser->fn_id_prefix = get_the_ID() . ".";
			}

			return $parser->transform($text);
		}

		# Comments
		# - Remove WordPress paragraph generator.
		# - Remove WordPress auto-link generator.
		# - Scramble important tags before passing them to the kses filter.
		# - Run Markdown on excerpt then remove paragraph tags.
		if(MARKDOWN_WP_COMMENTS) {
			remove_filter('comment_text', 'wpautop', 30);
			remove_filter('comment_text', 'make_clickable');
			add_filter('pre_comment_content', 'Markdown', 6);
			add_filter('pre_comment_content', 'mdwp_hide_tags', 8);
			add_filter('pre_comment_content', 'mdwp_show_tags', 12);
			add_filter('get_comment_text', 'Markdown', 6);
			add_filter('get_comment_excerpt', 'Markdown', 6);
			add_filter('get_comment_excerpt', 'mdwp_strip_p', 7);

			global $mdwp_hidden_tags, $mdwp_placeholders;
			$mdwp_hidden_tags = explode(' ',
				'<p> </p> <pre> </pre> <ol> </ol> <ul> </ul> <li> </li>');
			$mdwp_placeholders = explode(' ', str_rot13(
				'pEj07ZbbBZ U1kqgh4w4p pre2zmeN6K QTi31t9pre ol0MP1jzJR ' .
					'ML5IjmbRol ulANi1NsGY J7zRLJqPul liA8ctl16T K9nhooUHli'));
		}

		function mdwp_add_p($text) {
			if(!preg_match('{^$|^<(p|ul|ol|dl|pre|blockquote)>}i', $text)) {
				$text = '<p>' . $text . '</p>';
				$text = preg_replace('{\n{2,}}', "</p>\n\n<p>", $text);
			}

			return $text;
		}

		function mdwp_strip_p($t) {
			return preg_replace('{</?p>}i', '', $t);
		}

		function mdwp_hide_tags($text) {
			global $mdwp_hidden_tags, $mdwp_placeholders;

			return str_replace($mdwp_hidden_tags, $mdwp_placeholders, $text);
		}

		function mdwp_show_tags($text) {
			global $mdwp_hidden_tags, $mdwp_placeholders;

			return str_replace($mdwp_placeholders, $mdwp_hidden_tags, $text);
		}
	}


	### bBlog Plugin Info ###

	function identify_modifier_markdown() {
		return array(
			'name' => 'markdown',
			'type' => 'modifier',
			'nicename' => 'PHP Markdown Extra',
			'description' => 'A text-to-HTML conversion tool for web writers',
			'authors' => 'Michel Fortin and John Gruber',
			'licence' => 'GPL',
			'version' => MARKDOWNEXTRA_VERSION,
			'help' => '<a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a> allows you to write using an easy-to-read, easy-to-write plain text format. Based on the original Perl version by <a href="http://daringfireball.net/">John Gruber</a>. <a href="http://michelf.com/projects/php-markdown/">More...</a>',
		);
	}


	### Smarty Modifier Interface ###

	function smarty_modifier_markdown($text) {
		return Markdown($text);
	}


	### Textile Compatibility Mode ###

	# Rename this file to "classTextile.php" and it can replace Textile everywhere.

	if(strcasecmp(substr(__FILE__, -16), "classTextile.php") == 0) {
		# Try to include PHP SmartyPants. Should be in the same directory.
		@include_once 'smartypants.php';
		# Fake Textile class. It calls Markdown instead.
		class Textile {
			function TextileThis($text, $lite = '', $encode = '') {
				if($lite == '' && $encode == '') {
					$text = Markdown($text);
				}
				if(function_exists('SmartyPants')) {
					$text = SmartyPants($text);
				}

				return $text;
			}

			# Fake restricted version: restrictions are not supported for now.
			function TextileRestricted($text, $lite = '', $noimage = '') {
				return $this->TextileThis($text, $lite);
			}

			# Workaround to ensure compatibility with TextPattern 4.0.3.
			function blockLite($text) {
				return $text;
			}
		}
	}

?>