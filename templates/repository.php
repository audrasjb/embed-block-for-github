<div class="ebg-br-wrapper %%_WRAPPER_DARK_MODE_%%">
	<div class="ebg-br-background-image"></div>
	<div class="ebg-br-editmode egb-br-darkmode-status">
		<span class="egb-br-darkmode-status-img">Dark Mode</span>
	</div>
	<div class="ebg-br-avatar">
		<img class="ebg-br-header-avatar" src="%%_DATA_AVATAR_URL_%%" alt="" width="150" height="150" />
	</div>
	<div class="ebg-br-main">
		<p class="ebg-br-title">
			<strong>
				<a target="_blank" rel="noopener noreferrer" href="%%_DATA_REPO_URL_%%">
					%%_DATA_REPO_NAME_%%
					<span class="screen-reader-text">(<?php echo esc_html__( 'this link opens in a new window', 'embed-block-for-github' ); ?>)</span>
				</a>
			</strong>
			<em>
				<?php echo esc_html__( 'by', 'embed-block-for-github' ); ?><a target="_blank" rel="noopener noreferrer" href="%%_DATA_AUTOR_URL_%%">
					%%_DATA_AUTOR_NAME_%%
					<span class="screen-reader-text">(<?php echo esc_html__( 'this link opens in a new window', 'embed-block-for-github' ); ?>)</span>
				</a>
			</em>
		</p>
		<p class="ebg-br-description">%%_DATA_DESCIPTION_%%</p>
		<p class="ebg-br-footer">
			<span class="ebg-br-subscribers">
				<span class="ebg-br-background-image"></span>
				<?php echo esc_html( sprintf( _n( '%s Subscriber', '%s Subscribers', $data->subscribers_count, 'embed-block-for-github' ), $data->subscribers_count ) ); ?>
			</span>
			<span class="ebg-br-watchers">
				<span class="ebg-br-background-image"></span>
				<?php echo esc_html( sprintf( _n( '%s Watcher', '%s Watchers', $data->watchers_count, 'embed-block-for-github' ), $data->watchers_count ) ); ?>
			</span>
			<span class="ebg-br-forks">
				<span class="ebg-br-background-image"></span>
				<?php echo esc_html( sprintf( _n( '%s Fork', '%s Forks', $data->forks_count, 'embed-block-for-github' ), $data->forks_count ) ); ?>
			</span>
			<a target="_blank" rel="noopener noreferrer" class="ebg-br-link" href="%%_DATA_REPO_URL_%%">
				<?php echo esc_html__( 'Check out this repository on GitHub.com', 'embed-block-for-github' ); ?>
				<span class="screen-reader-text">(<?php echo esc_html__( 'this link opens in a new window', 'embed-block-for-github' ); ?>)</span>
			</a>
		</p>
	</div>
</div>