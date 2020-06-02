<?php
/*
Template Name: wp query筛选页
*/
get_header();

?>
<div id="filter">
	<div class="filter-item">
		<div class="filter-item">分类：
			<a href="<?php echo remove_query_arg('cat')?>" class="<?php if(!isset($_GET['cat'])) echo 'on';?>">不限</a>
			<a href="<?php echo add_query_arg('cat','id')?>" class="<?php if(isset($_GET['cat']) && $_GET['cat'] == 'id') echo 'on';?>">分类ID</a>
		</div>
	</div>
	<div class="filter-item">
		<div class="filter-item">标签：
			<a href="<?php echo remove_query_arg('tag')?>" class="<?php if(!isset($_GET['tag'])) echo 'on';?>">不限</a>
			<a href="<?php echo add_query_arg('tag','id')?>" class="<?php if(isset($_GET['tag']) && $_GET['tag'] == 'id') echo 'on';?>">标签ID</a>
		</div>
	</div>
	<div class="filter-item">
		<div class="filter-item">作者：
			<a href="<?php echo remove_query_arg('author')?>" class="<?php if(!isset($_GET['author'])) echo 'on';?>">不限</a>
			<a href="<?php echo add_query_arg('author','id')?>" class="<?php if(isset($_GET['author']) && $_GET['author'] == 'id') echo 'on';?>">作者ID</a>
		</div>
	</div>
	<div class="filter-item">
		<div class="filter-item">字段：
			<a href="<?php echo remove_query_arg('meta_key')?>" class="<?php if(!isset($_GET['meta_key'])) echo 'on';?>">不限</a>
			<a href="<?php echo add_query_arg('meta_key','meta_value')?>" class="<?php if(isset($_GET['meta_key']) && $_GET['meta_key'] == 'meta_value') echo 'on';?>">字段值</a>
		</div>
	</div>
	<div class="filter-item">
		<div class="filter-item">排序：
			<a href="<?php echo remove_query_arg('orderby')?>" class="<?php if(!isset($_GET['orderby'])) echo 'on';?>">不限</a>
			<a href="<?php echo add_query_arg('orderby','ID')?>" class="<?php if(isset($_GET['orderby']) && $_GET['orderby'] == 'ID') echo 'on';?>">文章ID</a>
			<a href="<?php echo add_query_arg('orderby','date')?>" class="<?php if(isset($_GET['orderby']) && $_GET['orderby'] == 'date') echo 'on';?>">发布日期</a>
			<a href="<?php echo add_query_arg('orderby','modified')?>" class="<?php if(isset($_GET['orderby']) && $_GET['orderby'] == 'modified') echo 'on';?>">修改日期</a>
			<a href="<?php echo add_query_arg('orderby','comment_count')?>" class="<?php if(isset($_GET['orderby']) && $_GET['orderby'] == 'comment_count') echo 'on';?>">评论数量</a>
			<a href="<?php echo add_query_arg('orderby','meta_value_num')?>" class="<?php if(isset($_GET['orderby']) && $_GET['orderby'] == 'meta_value_num') echo 'on';?>">自定义字段</a>
			<a href="<?php echo add_query_arg('orderby','rand')?>" class="<?php if(isset($_GET['orderby']) && $_GET['orderby'] == 'rand') echo 'on';?>">随机</a>
		</div>
	</div>
</div>
<!-- 前台html -->
<main class="inner-container">
	<?php
	$args ="'posts_per_page'=><span class=\"var\">10</span>,";
	if(isset($_GET['cat']))
		$args .="'cat' => <span class=\"var\">'分类ID'</span>,";
	if(isset($_GET['tag']))
		$args .="'tag_id' => <span class=\"var\">'标签id'</span>,";
	if(isset($_GET['author']))
		$args .="'author' => <span class=\"var\">'作者ID'</span>,";
	if(isset($_GET['meta_key']))
			$args .='\'meta_key\'=>\'<span class="var">'.$_GET['meta_key'].'</span>\',';
	if(isset($_GET['orderby'])){
		$args .='\'orderby\'=>'.'\''.$_GET['orderby'].'\',';
		if( $_GET['orderby'] =='meta_value_num' and !isset($_GET['meta_key']) )
			$args .='\'meta_key\'=>\'<span class="var">meta_key</span>\',';
	}
	?>

    <?php 
        $description='WordPress基于WP Query /  get_posts 获取文章列表：';

        if (isset($_GET['cat']))
            $description.='指定分类ID ';
        if (isset($_GET['tag']))
            $description.='指定标签ID ';
        if (isset($_GET['author']))
            $description.='指定作者ID ';        
        if (isset($_GET['meta_key']))
            $description.='指定Meta字段 ';
        if (isset($_GET['orderby'])){
            switch ($_GET['orderby']) {
                case 'ID':
                    $orderby = 'ID';
                break;
                
                case 'date':
                    $orderby = '最新';
                break;

                case 'modified':
                    $orderby = '修改时间';
                break;

                case 'comment_count':
                    $orderby = '评论数量';
                break;

                case 'meta_value_num':
                    $orderby = '字段值';
                break;

                case 'rand':
                    $orderby = '随机';
                break;

                default:
                    $orderby = '发布时间';    
            }
            $description.=' & 排序：'.$orderby;        
        }

        echo '<h2 class="notice">'.$description.'</h2>';
    
    ?>	
	<!-- <p>请讲红色变量替换为您的实际值</p> -->
	<pre>
		<code class="language-php">
	&lt;ul&gt;
	&lt;?php
		<?php echo '$args=array('.$args.');'.PHP_EOL;?>
		$query = new WP_Query( $args );
		while ($query->have_posts()) : $query->the_post();
		&#160;?&gt;
		&lt;li&gt;&lt;a href="&lt;?php the_permalink();&#160;?&gt;"&gt;&lt;?php the_title();&#160;?&gt;&lt;/a&gt;&lt;/li&gt;
	&lt;?php endwhile; wp_reset_postdata();?&gt;
	&lt;/ul&gt;
		</code>
	</pre>
    
    <?php 
    
    while(have_posts()): the_post();
    the_content();
    endwhile;
    
    ?>
</main>
<!--content-list-->
<?php get_footer(); ?>