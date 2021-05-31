# TYPO3 Extension `news_filter`

This extension makes it possible to filter news in the frontend by the following properties:

- date from & to
- categories
- tags

This extension has been sponsored by [University Basel](https://www.unibas.ch)

## Requirements

- TYPO3 8.7 - 9.5 (7 should work as well)
- news 7.x

## Usage

1. Install the extension just as any other extension. Either use the Extension Manager or composer and `composer require georgringer/news-filter`.
2. Select the action "list" in the news plugin and active the enable checkbox
3. Select page of categories & tags.

### Templating

Add the following part to your `List.html`:

```
	<f:form action="list" object="{extendedVariables.searchDemand}" name="search" class="form-horizontal">
		<fieldset>
			<div class="form-group">
				<f:for each="{extendedVariables.categories}" as="category">
					<div class="checkbox">
						<label>
							<f:form.checkbox property="filteredCategories" value="{category.uid}"/>
							{category.title}
						</label>
					</div>
				</f:for>
			</div>
			<div class="form-group">
				<f:for each="{extendedVariables.tags}" as="tag">
					<div class="tag">
						<label>
							<f:form.checkbox property="filteredTags" value="{tag.uid}"/>
							{tag.title}
						</label>
					</div>
				</f:for>
			</div>

			<div class="form-group">
				<label for="fromDate" class="col-sm-2 control-label">Date from</label>
				<div class="col-sm-10">
					<f:form.textfield type="date" class="form-control" id="fromDate" property="fromDate"/>
				</div>
			</div>
			<div class="form-group">
				<label for="toDate" class="col-sm-2 control-label">Date to</label>
				<div class="col-sm-10">
					<f:form.textfield type="date" class="form-control" id="toDate" property="toDate"/>
				</div>
			</div>

			<f:form.submit value="submit" class="btn btn-primary"/>
		</fieldset>
	</f:form>
```

### Order categories and tags

```typoscript
plugin.tx_news {
	settings {
		filterCategoriesOrderBy = title
		filterCategoriesOrderDirection = asc

		filterTagsOrderBy = title
		filterTagsOrderDirection = asc
	}
}
```
