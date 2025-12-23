const searchClient = algoliasearch('VPBCCIZ29A', 'a7677819ac27256bc58c2ce1c08c0e25');

// Returns a slug from the category name.
// Spaces are replaced by "+" to make
// the URL easier to read and other
// characters are encoded.
function getCategorySlug(name) {
    return name
      .split(' ')
      .map(encodeURIComponent)
      .join('+');
  }
  
  // Returns a name from the category slug.
  // The "+" are replaced by spaces and other
  // characters are decoded.
  function getCategoryName(slug) {
    return slug
      .split('+')
      .map(decodeURIComponent)
      .join(' ');
  }

const loadingContainer = document.querySelector('#loading');
const search = instantsearch({
    indexName: 'products',
    searchClient,
    stalledSearchDelay: 200,
    routing:true
});

search.addWidgets([
    // {
    //     render: ({ searchMetadata = {} }) => {
    //       const { isSearchStalled } = searchMetadata;
      
    //       loadingContainer.innerHTML = isSearchStalled ? 'loading' : '';
    //     },
    // },

    instantsearch.widgets.searchBox({
        container: '#searchbox',
        placeholder: 'Search for products, categories, ...',
        showLoadingIndicator: true, // this add the loading indicator
        templates: {
            loadingIndicator: 'loading',
        },
    }),


    instantsearch.widgets.clearRefinements({
        container: '#clear-refinements',
    }),

    instantsearch.widgets.configure({
        hitsPerPage: 20
    }),

    instantsearch.widgets.pagination({
        container: '#pagination',
        scrollTo: '#searchbox',
    }),
    instantsearch.widgets.refinementList({
        container: '#brand-list',
        attribute: 'category',
        sortBy: ['name:asc'],
        showMore: true,
        operator: 'or',
        showMoreLimit: 100,
        templates: {
            item: `
              <a href="{{url}}" style="{{#isRefined}}font-weight: bold;color: red{{/isRefined}}">
                <span>{{label}}</span>
              </a>
              <hr>
            `,
            showMoreText: `
                {{#isShowingMore}}
                    Show less brands
                {{/isShowingMore}}
                {{^isShowingMore}}
                    Show more brands
                {{/isShowingMore}}
            `,            
        }
    }),

    instantsearch.widgets.refinementList({
        container: '#case_size',
        attribute: 'case_size',
        showMore: true,
        templates: {
            item: `
              <a href="{{url}}" style="{{#isRefined}}font-weight: bold;color: red{{/isRefined}}">
                <span>{{label}}</span>
              </a><hr>
            `,
            showMoreText: `
                {{#isShowingMore}}
                    Show less sizes
                {{/isShowingMore}}
                {{^isShowingMore}}
                    Show more sizes
                {{/isShowingMore}}
            `,
        }

    }),
    instantsearch.widgets.refinementList({
        container: '#condition',
        attribute: 'condition',
        templates: {
            item: `
              <a href="{{url}}" style="{{#isRefined}}font-weight: bold;color: red{{/isRefined}}">
                <span>{{label}}</span>
              </a><hr>
            `,
        }

    }),
    instantsearch.widgets.refinementList({
        container: '#gender',
        attribute: 'gender',
        templates: {
            item: `
              <a href="{{url}}" style="{{#isRefined}}font-weight: bold;color: red{{/isRefined}}">
                <span>{{label}}</span>
              </a><hr>
            `,
        }
    }),

    instantsearch.widgets.hits({
        container: '#hits',
        // hitsPerPage: 6,
        // cssClasses: {
        //   root: 'row',
        //   item: 'col-lg-3 col-md-4 col-sm-6'
        // },
        templates: {
            item: data => {
                return `
            
            <div class="product-area">
                
                <div class="thumbnail">
                    <a href="${window.location.href.indexOf('chrono24')==-1 ? data.slug : "/chrono24/watches"+data.slug}">
                        <img title="${data.details}" alt="${data.details}" src="${data.image}">
                    </a>
                    <span class="sticker-wrapper top-left"><span class="sticker new" style="${data.status == 'On Memo' || data.status == 'At the Show' ? 'color:red' : 'color:green'}">${data.status=='Available' ? 'In Stock' : data.status}</span></span>
                    <button class="btn btn-secondary btn-sm" onclick="window.location.href='${window.location.href.indexOf('chrono24')==-1 ? data.slug : "/chrono24/watches"+data.slug}'" title="View details about ${data.details}" aria-pressed="false" autocomplete="off" style="width: 100%">View Details</button>

                </div>
                <div class="caption">
                    <a href="${window.location.href.indexOf('chrono24')==-1 ? data.slug : "/chrono24/watches"+data.slug}">${data.details}</a>
                </div>
                <div class="price-area">
                    <span class="${data.sale == 'sale'? 'price product_sale' : ''}">${data.price}</span>
                </div>
            </div>
                `;
            }
        }
    })
]);

search.start();
