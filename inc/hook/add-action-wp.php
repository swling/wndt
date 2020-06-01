<?php
//############################################################################ 以下为WordPress原生action

// 自定义代码高亮按钮
function appthemes_add_quicktags() {
	if (wp_script_is('quicktags')) {
		?>
	    <script type="text/javascript">
	    // wp editor 添加代码高亮
	    QTags.addButton('eg_hlAll','*ALL','<pre><code class="language-">','</code></pre>');
	    QTags.addButton('eg_hlCSS','*CSS','<pre><code class="language-css">','</code></pre>');
	    QTags.addButton('eg_hlJS','*JS','<pre><code class="language-JavaScript">','</code></pre>');
	    QTags.addButton('eg_hPHP','*PHP','<pre><code class="language-php">','</code></pre>');
	    </script>
	<?php
}
}
add_action('admin_print_footer_scripts', 'appthemes_add_quicktags');
add_action('wp_print_footer_scripts', 'appthemes_add_quicktags');
