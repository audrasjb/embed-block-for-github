<div class="ebg-br-wrapper %%_WRAPPER_DARK_MODE_%%">
	<div class="ebg-br-background ebg-br-img-color-auto ebg-br-ico-github"></div>
	<div class="ebg-br-avatar">
		<img class="ebg-br-header-avatar" src="%%_DATA_REPO_OWNER_AVATAR_URL_%%" alt="" width="150" height="150" />
	</div>
	<div class="ebg-br-main">
		<p class="ebg-br-title">
			<strong>
				<a target="_blank" rel="noopener noreferrer" href="%%_DATA_REPO_HTML_URL_%%">
					%%_DATA_REPO_NAME_%%
					<img class="ebg-br-img-color-auto" src="%%_URL_ICO_LINK_%%" alt="" height="13" width="13">
					<span class="screen-reader-text">(<?php echo esc_html__( 'this link opens in a new window', 'embed-block-for-github' ); ?>)</span>
				</a>
			</strong>
			<em>
				<?php echo esc_html__( 'by', 'embed-block-for-github' ); ?><a target="_blank" rel="noopener noreferrer" href="%%_DATA_REPO_OWNER_HTML_URL_%%">
					%%_DATA_REPO_OWNER_LOGIN_%%
					<img class="ebg-br-img-color-auto" src="%%_URL_ICO_LINK_%%" alt="" height="9" width="9">
					<span class="screen-reader-text">(<?php echo esc_html__( 'this link opens in a new window', 'embed-block-for-github' ); ?>)</span>
				</a>
			</em>
		</p>
		<p class="ebg-br-description">%%_DATA_REPO_DESCRIPTION_%%</p>
		<p class="ebg-br-footer">
			<span class="ebg-br-subscribers">
				<span class="ebg-br-ico-mini ebg-br-img-color-auto ebg-br-ico-subscribers"></span>
				<?php echo esc_html( sprintf( _n( '%s Subscriber', '%s Subscribers', $data->subscribers_count, 'embed-block-for-github' ), $data->subscribers_count ) ); ?>
			</span>
			<span class="ebg-br-watchers">
				<span class="ebg-br-ico-mini ebg-br-img-color-auto ebg-br-ico-watchers"></span>
				<?php echo esc_html( sprintf( _n( '%s Watcher', '%s Watchers', $data->watchers_count, 'embed-block-for-github' ), $data->watchers_count ) ); ?>
			</span>
			<span class="ebg-br-forks">
				<span class="ebg-br-ico-mini ebg-br-img-color-auto ebg-br-ico-forks"></span>
				<?php echo esc_html( sprintf( _n( '%s Fork', '%s Forks', $data->forks_count, 'embed-block-for-github' ), $data->forks_count ) ); ?>
			</span>
			<a target="_blank" rel="noopener noreferrer" class="ebg-br-link" href="%%_DATA_REPO_HTML_URL_%%">
				<?php echo esc_html__( 'Check out this repository on GitHub.com', 'embed-block-for-github' ); ?>
				<img class="ebg-br-img-color-auto" src="%%_URL_ICO_LINK_%%" alt="" height="11" width="11">
				<span class="screen-reader-text">(<?php echo esc_html__( 'this link opens in a new window', 'embed-block-for-github' ); ?>)</span>
			</a>
		</p>
	</div>
	<div class="ebg-br-editmode egb-br-darkmode-status">
		<span class="egb-br-darkmode-status-img"><?php echo esc_html__( 'Status Dark Mode', 'embed-block-for-github' ); ?></span>
	</div>
</div>