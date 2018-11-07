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
2. Select the action "news filter" in the news plugin
3. Select page of categories & tags.

### Templating

Either copy the partial and template of `EXT:news_filter/Resources/Private/` into the templates of news in your side project or use the following TypoScript

```
plugin.tx_news {
	view {
		templateRootPaths {
			91 = EXT:news_filter/Resources/Private/Templates/
		}

		partialRootPaths {
			91 = EXT:news_filter/Resources/Private/Partials/
		}
	}
```

