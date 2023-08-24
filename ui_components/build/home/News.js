import { apiFetch } from "../utils/network.js";
import { clickOnEnter } from "../utils/uiHelpers.js";
export function News({
  initialNewsPosts,
  isAdmin,
  version,
  homeLinks
}) {
  const [activePostId, setActivePostId] = React.useState(initialNewsPosts[0] !== "undefined" ? initialNewsPosts[0].post_id : null);
  const [editPostId, setEditPostId] = React.useState(null);
  const numPosts = React.useRef(initialNewsPosts.length);
  const [newsPosts, setNewsPosts] = React.useState(initialNewsPosts);
  const titleRef = React.useRef(null);
  const versionRef = React.useRef(null);
  const contentRef = React.useRef(null);
  const updateTagRef = React.useRef(null);
  const bugfixTagRef = React.useRef(null);
  const eventTagRef = React.useRef(null);
  function formatNewsDate(ticks) {
    const date = new Date(ticks * 1000);
    return date.toLocaleDateString('en-US', {
      month: '2-digit',
      day: '2-digit',
      year: '2-digit'
    });
  }
  function cleanNewsContents(contents) {
    const parser = new DOMParser();
    return parser.parseFromString(contents.replace(/[\r\n]+/g, " ").replace(/<br\s*\/?>/g, '\n'), 'text/html').body.textContent;
  }
  function saveNewsItem(postId) {
    numPosts.current = numPosts.current + 1;
    apiFetch(homeLinks.news_api, {
      request: 'saveNewsPost',
      post_id: postId,
      title: titleRef.current.textContent,
      version: versionRef.current.textContent,
      content: contentRef.current.value,
      update: updateTagRef.current.checked,
      bugfix: bugfixTagRef.current.checked,
      event: eventTagRef.current.checked,
      num_posts: numPosts.current
    }).then(response => {
      if (response.errors.length) {
        console.warn(response.errors);
      } else {
        setNewsPosts(response.data.postData);
      }
    });
    setEditPostId(null);
  }
  function loadNews() {
    numPosts.current = numPosts.current + 2;
    apiFetch(homeLinks.news_api, {
      request: 'getNewsPosts',
      num_posts: numPosts.current
    }).then(response => {
      if (response.errors.length) {
        console.warn(response.errors);
      } else {
        setNewsPosts(response.data.postData);
      }
    });
  }
  function createPost() {
    const newPost = {
      post_id: 0,
      title: "New Post",
      sender: "YOU",
      time: Math.floor(new Date().getTime() / 1000),
      version: version,
      message: "Edit Content",
      tags: []
    };
    const updatedPosts = [...newsPosts];
    updatedPosts.push(newPost);
    setNewsPosts(updatedPosts);
  }
  function NewsItem({
    newsItem
  }) {
    return /*#__PURE__*/React.createElement("div", {
      className: "news_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: activePostId === newsItem.post_id ? "news_item_header" : "news_item_header news_item_header_minimized",
      onClick: () => setActivePostId(newsItem.post_id),
      role: "button",
      tabIndex: "0",
      onKeyPress: clickOnEnter
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_title"
    }, newsItem.title.toUpperCase()), /*#__PURE__*/React.createElement("div", {
      className: "news_item_version"
    }, newsItem.version && newsItem.version.toUpperCase()), newsItem.tags.map((tag, index) => /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, tag.toUpperCase()))), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details_container"
    }, isAdmin && /*#__PURE__*/React.createElement("div", {
      className: "news_item_edit",
      onClick: () => setEditPostId(newsItem.post_id)
    }, "EDIT"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details"
    }, "POSTED ", formatNewsDate(newsItem.time), " BY ", newsItem.sender.toUpperCase()))), activePostId === newsItem.post_id && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "news_item_banner"
    }), /*#__PURE__*/React.createElement("div", {
      className: "news_item_content",
      dangerouslySetInnerHTML: {
        __html: newsItem.message
      }
    })));
  }
  function NewsItemEdit({
    newsItem
  }) {
    return /*#__PURE__*/React.createElement("div", {
      className: "news_item_editor"
    }, /*#__PURE__*/React.createElement("div", {
      className: activePostId === newsItem.post_id ? "news_item_header" : "news_item_header news_item_header_minimized",
      onClick: () => setActivePostId(newsItem.post_id)
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_title",
      ref: titleRef,
      contentEditable: "true",
      suppressContentEditableWarning: true,
      style: {
        minWidth: "25px"
      }
    }, newsItem.title.toUpperCase()), /*#__PURE__*/React.createElement("div", {
      className: "news_item_version",
      ref: versionRef,
      contentEditable: "true",
      suppressContentEditableWarning: true,
      style: {
        minWidth: "25px"
      }
    }, newsItem.version && newsItem.version.toUpperCase()), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, "UPDATE"), /*#__PURE__*/React.createElement("input", {
      id: "news_tag_update",
      type: "checkbox",
      ref: updateTagRef,
      defaultChecked: newsItem.tags.includes("update")
    })), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, "BUG FIXES"), /*#__PURE__*/React.createElement("input", {
      id: "news_tag_bugfixes",
      type: "checkbox",
      ref: bugfixTagRef,
      defaultChecked: newsItem.tags.includes("bugfix")
    })), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, "EVENT"), /*#__PURE__*/React.createElement("input", {
      id: "news_tag_event",
      type: "checkbox",
      ref: eventTagRef,
      defaultChecked: newsItem.tags.includes("event")
    })), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details_container"
    }, isAdmin && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "news_item_edit",
      onClick: () => setEditPostId(null)
    }, "CANCEL"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_edit",
      onClick: () => saveNewsItem(newsItem.post_id)
    }, "SAVE")), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details"
    }, "POSTED ", formatNewsDate(newsItem.time), " BY ", newsItem.sender.toUpperCase()))), activePostId === newsItem.post_id && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "news_item_banner"
    }), /*#__PURE__*/React.createElement("textarea", {
      className: "news_item_content_editor",
      ref: contentRef,
      defaultValue: cleanNewsContents(newsItem.message)
    })));
  }
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "news_posts_container"
  }, newsPosts && newsPosts.map(newsItem => newsItem.post_id === editPostId ? /*#__PURE__*/React.createElement(NewsItemEdit, {
    key: `news_post:${newsItem.post_id}`,
    newsItem: newsItem
  }) : /*#__PURE__*/React.createElement(NewsItem, {
    key: `news_post:${newsItem.post_id}`,
    newsItem: newsItem
  }))), /*#__PURE__*/React.createElement(NewsButtons, {
    loadNews: loadNews,
    createPost: createPost,
    isAdmin: isAdmin
  }));
}
function NewsButtons({
  loadNews,
  createPost,
  isAdmin
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "news_button_container"
  }, /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    name: "morenews",
    className: "morenews_button",
    width: "162",
    height: "32",
    onClick: () => loadNews(),
    onKeyPress: clickOnEnter,
    style: {
      zIndex: 2
    }
  }, /*#__PURE__*/React.createElement("radialGradient", {
    id: "morenews_fill_default",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#84314e',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#68293f',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("radialGradient", {
    id: "morenews_fill_click",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#68293f',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#84314e',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("rect", {
    className: "morenews_button_background",
    width: "100%",
    height: "100%",
    fill: "url(#morenews_fill_default)"
  }), /*#__PURE__*/React.createElement("text", {
    className: "morenews_button_shadow_text",
    x: "81",
    y: "18",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "more news"), /*#__PURE__*/React.createElement("text", {
    className: "morenews_button_text",
    x: "81",
    y: "16",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "more news")), isAdmin && /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    name: "createpost",
    className: "createpost_button",
    width: "162",
    height: "32",
    onClick: () => createPost(),
    onKeyPress: clickOnEnter,
    style: {
      zIndex: 4
    }
  }, /*#__PURE__*/React.createElement("radialGradient", {
    id: "createpost_fill_default",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#464f87',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#343d77',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("radialGradient", {
    id: "createpost_fill_click",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#343d77',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#464f87',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("rect", {
    className: "createpost_button_background",
    width: "100%",
    height: "100%"
  }), /*#__PURE__*/React.createElement("text", {
    className: "createpost_button_shadow_text",
    x: "81",
    y: "18",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "create post"), /*#__PURE__*/React.createElement("text", {
    className: "createpost_button_text",
    x: "81",
    y: "16",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "create post")));
}