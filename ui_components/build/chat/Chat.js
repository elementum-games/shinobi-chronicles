import { apiFetch } from "../utils/network.js";
const chatRefreshInterval = 5000;

function Chat({
  chatApiLink,
  initialPosts,
  initialNextPageIndex,
  initialMaxPostIndex,
  maxPostLength,
  isModerator,
  initialBanInfo
}) {
  const [banInfo, setBanInfo] = React.useState(initialBanInfo);
  const [posts, setPosts] = React.useState(initialPosts);
  const [previousPageIndex, setPreviousPageIndex] = React.useState(0);
  const [currentPageIndex, setCurrentPageIndex] = React.useState(0);
  const [nextPageIndex, setNextPageIndex] = React.useState(initialNextPageIndex);
  const [maxPostIndex, setMaxPostIndex] = React.useState(initialMaxPostIndex);
  const [error, setError] = React.useState(null);

  if (banInfo.isBanned) {
    return /*#__PURE__*/React.createElement(ChatBanInfo, {
      banName: banInfo.name,
      banDescription: banInfo.description,
      banTimeRemaining: banInfo.timeRemaining
    });
  }

  function getPosts() {
    apiFetch('/api/chat.php', {
      request: 'load_posts'
    }).then(handleApiResponse);
  }

  function submitPost(message) {
    apiFetch('/api/chat.php', {
      request: 'submit_post',
      message: message
    }).then(handleApiResponse);
  }

  function deletePost(postId) {
    apiFetch('/api/chat.php', {
      request: 'delete_post',
      post_id: postId
    }).then(handleApiResponse);
  }

  React.useEffect(() => {
    const intervalId = setInterval(getPosts, chatRefreshInterval);
    return () => clearInterval(intervalId);
  }, []);

  const handleApiResponse = response => {
    if (response.data.banInfo && response.data.banInfo.isBanned) {
      setBanInfo(response.data.banInfo);
      return;
    }

    if (response.errors.length > 0) {
      setError(response.errors.join(' '));
    } else {
      setError(null);
    }

    if (response.data.posts != null) {
      setPosts(response.data.posts);
    }

    if (response.data.previousPageIndex != null) {
      setPreviousPageIndex(response.data.previousPageIndex);
    }

    if (response.data.currentPageIndex != null) {
      setCurrentPageIndex(response.data.currentPageIndex);
    }

    if (response.data.nextPageIndex != null) {
      setNextPageIndex(response.data.nextPageIndex);
    }

    if (response.data.maxPostIndex != null) {
      setMaxPostIndex(response.data.maxPostIndex);
    }
  };

  return /*#__PURE__*/React.createElement("div", null, error != null && /*#__PURE__*/React.createElement("p", {
    className: "systemMessage"
  }, error), /*#__PURE__*/React.createElement(ChatInput, {
    maxPostLength: maxPostLength,
    submitPost: submitPost
  }), /*#__PURE__*/React.createElement(ChatPosts, {
    posts: posts,
    previousPostIndex: previousPageIndex,
    currentPageIndex: currentPageIndex,
    nextPageIndex: nextPageIndex,
    maxPostIndex: maxPostIndex,
    isModerator: isModerator,
    deletePost: deletePost
  }));
}

function ChatBanInfo({
  banName,
  banDescription,
  banTimeRemaining
}) {
  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, banName)), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, banDescription, banTimeRemaining != null && /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, "Time Remaining:"), " ", banTimeRemaining), /*#__PURE__*/React.createElement("br", null), "Visit the ", /*#__PURE__*/React.createElement("a", {
    href: "/support.php"
  }, "Support Center"), " to appeal this."))));
}

function ChatInput({
  maxPostLength,
  submitPost
}) {
  const [quickReply, _setQuickReply] = React.useState(JSON.parse(localStorage.getItem("quick_reply_on") ?? "true"));
  const [message, setMessage] = React.useState("");
  /*$(document).on("click", ".meme_select", function () {
      // Chat.val(Chat.val() + $(this).attr("data-code"));
      $("#meme_modal").addClass("hidden");
      $("#meme_toggle").text("+ Meme");
  });
  $(document).on("click", ".meme_toggle", function (e) {
      $("#meme_modal").toggleClass("hidden");
  });*/

  function setQuickReply(newValue) {
    localStorage.setItem("quick_reply_on", JSON.stringify(newValue));

    _setQuickReply(newValue);
  }

  const handlePostSubmit = React.useCallback(() => {
    submitPost(message); // TODO: Only clear this on a successful server response

    setMessage("");
  }, [message, submitPost]);
  const handleKeyDown = React.useCallback(e => {
    if (e.which !== 13) {
      return;
    }

    if (quickReply && !e.shiftKey) {
      e.preventDefault();
      handlePostSubmit();
    }
  }, [quickReply, message, submitPost]);

  function handleInputFocus() {
    document.addEventListener('keydown', handleKeyDown);
  }

  function handleInputBlur() {
    document.removeEventListener('keydown', handleKeyDown);
  }

  const charactersRemaining = maxPostLength - message.length;
  const charactersRemainingDisplay = `Characters remaining: ${charactersRemaining} of ${maxPostLength}`;
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("table", {
    id: "chat_input_table",
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Post Message")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: "meme_toggle"
  }, "Meme"), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("textarea", {
    id: "chat_input_box",
    minLength: "3",
    maxLength: maxPostLength,
    value: message,
    onChange: e => setMessage(e.target.value),
    onFocus: handleInputFocus,
    onBlur: handleInputBlur
  }), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: quickReply,
    onChange: e => setQuickReply(e.target.checked)
  }), " Quick reply", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("span", {
    className: "red"
  }, charactersRemaining < 50 && charactersRemainingDisplay), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("button", {
    onClick: handlePostSubmit
  }, "Post"))))));
}

function ChatMemeModal({
  memes
}) {
  const memeCodes = memes.map(meme => meme.code);
  return /*#__PURE__*/React.createElement("table", {
    id: "meme_modal",
    className: "table hidden"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Memes")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    id: "meme_box"
  }, memes.map((meme, i) => /*#__PURE__*/React.createElement("div", {
    key: `meme:${i}`,
    "data-code": meme.code,
    className: "meme_select"
  }, meme.image))))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: "meme_toggle"
  }, "Close")))));
}

function ChatPosts({
  posts,
  currentPageIndex,
  maxPostIndex,
  isModerator,
  deletePost
}) {
  function handlePreviousClick() {}

  function handleNextClick() {}

  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("table", {
    className: "table",
    style: {
      width: "98%"
    }
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", {
    style: {
      width: "28%"
    }
  }, "Users"), /*#__PURE__*/React.createElement("th", {
    style: {
      width: "61%"
    }
  }, "Message"), /*#__PURE__*/React.createElement("th", {
    style: {
      width: "10%"
    }
  }, "Time")), posts.length < 1 && /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    colSpan: "3",
    style: {
      textAlign: "center"
    }
  }, "No posts!")), posts.map((post, i) => /*#__PURE__*/React.createElement("tr", {
    key: `post:${i}`,
    className: "chat_msg",
    style: {
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    id: "user_data_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "avatarContainer"
  }, /*#__PURE__*/React.createElement("img", {
    src: post.avatarLink
  })), /*#__PURE__*/React.createElement("div", {
    className: "character_info"
  }, /*#__PURE__*/React.createElement("a", {
    href: post.userProfileLink,
    className: `${post.class} ${post.statusType}`
  }, post.userName), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("img", {
    className: "villageIcon",
    src: `./images/village_icons/${post.userVillage.toLowerCase()}.png`,
    alt: `${post.userVillage} Village`,
    title: `${post.userVillage} Village`
  }), post.userTitle))), post.staffBannerName && /*#__PURE__*/React.createElement("p", {
    className: "staffMember",
    style: {
      backgroundColor: post.staffBannerColor
    }
  }, post.staffBannerName)), /*#__PURE__*/React.createElement("td", {
    dangerouslySetInnerHTML: {
      __html: post.message
    }
  }), /*#__PURE__*/React.createElement("td", {
    style: {
      fontStyle: "italic"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: "2px"
    }
  }, post.timeString), isModerator && /*#__PURE__*/React.createElement("a", {
    className: "imageLink",
    onClick: () => deletePost(post.id)
  }, /*#__PURE__*/React.createElement("img", {
    className: "small_image",
    src: "../images/delete_icon.png"
  })), /*#__PURE__*/React.createElement("a", {
    className: "imageLink",
    href: post.reportLink
  }, /*#__PURE__*/React.createElement("img", {
    className: "small_image",
    src: "../images/report_icon.png"
  }))))))), /*#__PURE__*/React.createElement("p", {
    style: {
      textAlign: "center"
    }
  }, currentPageIndex > 0 && /*#__PURE__*/React.createElement("a", {
    onClick: handlePreviousClick
  }, "Previous"), currentPageIndex < maxPostIndex && /*#__PURE__*/React.createElement(React.Fragment, null, currentPageIndex !== 0 && "&nbsp;&nbsp;|&nbsp;&nbsp;", /*#__PURE__*/React.createElement("a", {
    onClick: handleNextClick
  }, "Next"))));
}

window.Chat = Chat;