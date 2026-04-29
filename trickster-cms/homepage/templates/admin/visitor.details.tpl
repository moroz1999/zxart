<div class="tabs_block">
	<div class="tabs_list"></div>
	<div class="tabs_content">
		<div class="visitordetails">
			{function printSimpleDetail key=''}
				<tr>
					<th>
						{translations name="visitor.$key"}:
					</th>
					<td>
						{$visitor->$key|default:'N/A'}
					</td>
				</tr>
			{/function}
			<div class="panel_component">
				<div class="panel_heading">{translations name="visitor.rundown"}</div>
				<div class="panel_content">
					<table class="visitordetails_table table_component">
						<tbody>
						{call printSimpleDetail key='id'}
						{call printSimpleDetail key='trackingCode'}
						{call printSimpleDetail key='email'}
						{call printSimpleDetail key='firstName'}
						{call printSimpleDetail key='lastName'}
						{call printSimpleDetail key='phone'}
						<tr>
							<th>
								{translations name='visitor.user'}:
							</th>
							<td>
								{if $user = $visitor->getUser()}
									<a href="{$user->URL}">{$user->getTitle()}</a>
								{else}
									N/A
								{/if}
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
			{if !empty($visitor->getVisits())}
				<div class="panel_component">
					<div class="panel_heading">{translations name="visitor.visits"}</div>
					<div class="panel_content">
						<table class="visitordetails_table table_component">
							<thead>
							<tr>
								<th>
									{translations name='visitor.visit_time'}
								</th>
								<th>
									{translations name='visitor.visit_referer'}
								</th>
							</tr>
							</thead>
							<tbody>
							{foreach $visitor->getVisits() as $visit}
								<tr>
									<td>
										{date('d.m.Y H:i', $visit.time)}
									</td>
									<td>
										{$visit.referer}
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
			</div>
			{/if}
			{$orders = $visitor->getOrders()}
			{if !empty($orders.orders)}
				<div class="panel_component">
					<div class="panel_heading">{translations name="visitor.orders"}</div>
					<div class="panel_content">
						<table class="visitordetails_table table_component">
							<thead>
							<tr>
								<th>
									{translations name='visitor.order_code'}
								</th>
								<th>
									{translations name='visitor.order_created_at'}
								</th>
								<th>
									{translations name='visitor.order_total'}
								</th>
							</tr>
							</thead>
							<tbody>

							{foreach $orders.orders as $order}
								<tr>
									<td>
										<a href="{$order->URL}">{$order->getTitle()}</a>
									</td>
									<td>
										{$order->dateCreated}
									</td>
									<td>
										{$order->getTotalPrice()} €
									</td>
								</tr>
							{/foreach}
							</tbody>
							<tfoot>
							<tr>
								<th colspan="2">{translations name='visitor.orders_total'}</th>
								<td>{number_format($orders.ordersTotal, 2, '.',' ')} €</td>
							</tr>
							</tfoot>
						</table>
					</div>
				</div>
				{if !empty($orders.orderProducts)}
					<div class="panel_component">
						<div class="panel_heading">{translations name="visitor.orderproducts"}</div>
						<div class="panel_content">
							<table class="visitordetails_table table_component">
								<thead>
								<tr>
									<th>
										{translations name='visitor.orderproduct_title'}
									</th>
									<th>
										{translations name='visitor.orderproduct_created_at'}
									</th>
									<th>
										{translations name='visitor.orderproduct_price'}
									</th>
									<th>
										{translations name='visitor.orderproduct_qty'}
									</th>
									<th>
										{translations name='visitor.orderproduct_total'}
									</th>
								</tr>
								</thead>
								<tbody>
								{foreach $orders.orderProducts as $product}
									<tr>
										<td>
											<a href="{$product->URL}">{$product->getTitle()}</a>
										</td>
										<td>
											{$product->dateCreated}
										</td>
										<td>
											{$product->price} €
										</td>
										<td>
											{$product->amount}
										</td>
										<td>
											{$product->getTotalPrice(true)} €
										</td>
									</tr>
								{/foreach}
								</tbody>
							</table>
						</div>
					</div>
				{/if}
			{/if}

			{if !empty($visitor->getMostViewedCategories())}
				<div class="panel_component">
					<div class="panel_heading">{translations name="visitor.mostViewedCategories"}</div>
					<div class="panel_content">
						<table class="visitordetails_table table_component">
							<thead>
							<tr>
								<th>
									{translations name='visitor.element_title'}
								</th>
								<th>
									{translations name='visitor.element_views'}
								</th>
							</tr>
							</thead>
							<tbody>
							{foreach $visitor->getMostViewedCategories() as $info}
								<tr>
									<td>
										<a href="{$info.url}">{$info.title}</a>
									</td>
									<td>
										{$info.views}
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			{/if}

			{if !empty($visitor->getMostViewedProducts())}
				<div class="panel_component">
					<div class="panel_heading">{translations name="visitor.mostViewedProducts"}</div>
					<div class="panel_content">
						<table class="visitordetails_table table_component">
							<thead>
							<tr>
								<th>
									{translations name='visitor.element_title'}
								</th>
								<th>
									{translations name='visitor.element_views'}
								</th>
							</tr>
							</thead>
							<tbody>
							{foreach $visitor->getMostViewedProducts() as $info}
								<tr>
									<td>
										<a href="{$info.url}">{$info.title}</a>
									</td>
									<td>
										{$info.views}
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			{/if}
			{if $products = $visitor->getAddedProductsToShoppingBasket()}
				<div class="panel_component">
					<div class="panel_heading">{translations name="visitor.addedProductsToBasket"}</div>
					<div class="panel_content">
						<table class="visitordetails_table table_component">
							<thead>
							<tr>
								<th>
									{translations name='visitor.element_title'}
								</th>
							</tr>
							</thead>
							<tbody>
							{foreach $products as $info}
								<tr>
									<td>
										<a href="{$info.url}">{$info.title}</a>
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			{/if}
			{if $emailClick = $visitor->getEmailClicks()}
				<div class="panel_component">
					<div class="panel_heading">{translations name="visitor.emailClicks"}</div>
					<div class="panel_content">
						<table class="visitordetails_table table_component">
							<thead>
							<tr>
								<th>
									{translations name='visitor.element_title'}
								</th>
								<th>
									{translations name='visitor.element_total'}
								</th>
							</tr>
							</thead>
							<tbody>
							{foreach $emailClick as $key=>$info}
								<tr>
									<td>
										<a href="{$key}">{$key}</a>
									</td>
									<td>
										{$info}
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			{/if}
			{if $emailClick = $visitor->getSearchLog()}
				<div class="panel_component">
					<div class="panel_heading">{translations name="visitor.searchQuery"}</div>
					<div class="panel_content">
						<table class="visitordetails_table table_component">
							<thead>
							<tr>
								<th>
									{translations name='visitor.element_title'}
								</th>
								<th>
									{translations name='visitor.element_total'}
								</th>
							</tr>
							</thead>
							<tbody>
							{foreach $emailClick as $key=>$info}
								<tr>
									<td>
										{$key}
									</td>
									<td>
										{$info}
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			{/if}
			{if $feedbacks = $visitor->getFeedbacks()}
				<div class="panel_component">
					<div class="panel_heading">{translations name="visitor.feedbacks"}</div>
					<div class="panel_content">
						<table class="visitordetails_table table_component">
							<thead>
								{$feedback = current($feedbacks)}
								<tr>
									{foreach $feedback as $key=>$field}
										<th>
											{$key}
										</th>
									{/foreach}
								</tr>
							</thead>
							<tbody>
							{foreach $feedbacks as $feedback}
								<tr>
									{foreach $feedback as $key=>$field}
										<td>
											{$field}
										</td>
									{/foreach}
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			{/if}
		</div>
	</div>
</div>
