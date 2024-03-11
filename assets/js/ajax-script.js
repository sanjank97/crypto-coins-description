let searchValue, timer = 0;
jQuery(document).ready(function ($) {

  coinList();
  $("#sync_all_coins").click(function () {
    $.ajax({
      url: crypto_desc_url.ajax_url,
      type: "POST",
      data: {
        action: "sync_coins_list",
      },
      success: function (data) {
        alert("Coins rows has been updated");
      },
    });
  });

  $(document).on("click", ".get_description", function () {
    let coin_id = $(this).attr("coin-id");
    $.ajax({
      url: crypto_desc_url.ajax_url,
      type: "POST",
      data: {
        action: "get_coins_decription",
        coin_id: coin_id
      },
      success: function (data) {
        $(`#description_${coin_id}`).html(JSON.parse(data))
      },
    });
  });


  $(document).on("click", ".update_description", function () {
    let coin_id = $(this).attr("coin-id");
    let coin_description = $(`#description_${coin_id}`).val();
    $.ajax({
      url: crypto_desc_url.ajax_url,
      type: "POST",
      data: {
        action: "update_coins_decription",
        coin_id: coin_id,
        coin_description: coin_description,
      },
      success: function (response) {
        var result = JSON.parse(response);
        if (result.status == "success") {
          alert('Updated');
          coinList();
        } else {
          alert('Failed');
        }
      },
    });
  });

  $('#search-crypto-coin').on('keyup', delay(function (e) {
    console.log("searchVlaue", searchValue);
    searchValue = jQuery(this).val();
    if (searchValue != "") {
      $.ajax({
        url: crypto_desc_url.ajax_url,
        type: 'POST',
        data: {
          action: 'get_search_list',
          search_data: searchValue,
        },
        success: function (data) {
          appendTr(data)
        },
        error: function (xhr, status, error) {

        }
      });
    } else {
      coinList();
    }
  }));

  $('input[type=search]').on('search', function () {
    if ($(this).val() == "") {
      coinList()
    }
  });

});

function coinList() {

  var urlParams = new URLSearchParams(window.location.search);
  var pageParam = urlParams.get('page');
  var pagedParam = urlParams.get('paged');

  $.ajax({
    url: crypto_desc_url.ajax_url,
    type: 'POST',
    data: {
      action: 'get_coins_list',
      page_num: pagedParam,
      items_per_page: 10
    },
    success: function (data) {
      appendTr(data);
      $('.crypto-description-cointainer .pagination').css("display", "block");
    }
  });

}

function appendTr(data) {
  $('#crypto_coins_description_rows').html("");
  myObject = JSON.parse(data);
  for (let key in myObject) {
    if (myObject.hasOwnProperty(key)) {
      let item = myObject[key];
      let tableHtml = `<tr>
                                      <td data-column="rank_key">${item.id}</td>
                                      <td data-column="Coin Name" data-id="${item.coin_id}">${item.coin_name}</td>
                                      <td data-column="Coin Description">
                                          <textarea id= "description_${item.coin_id}" rows="10" cols="60">${item.coin_description}</textarea>
                                          <button class="get_description" data-id="${item.id}" coin-id ="${item.coin_id}" >Get Description</button>
                                          <button class="update_description" data-id="${item.id}" coin-id ="${item.coin_id}" >Update Description</button>
                                      </td>
                                      <td data-column="description-status">${(item.coin_status == 0) ? 'Not Update' : 'Updated'}</td>
                                      <td data-column="description-update">${(item.coin_status == 0) ? '---' : item.updated_at}</td>
                                  </tr>`;
      $('#crypto_coins_description_rows').append(tableHtml);

    }
  }
}

function delay(callback, ms) {
  return function () {
    var context = this,
      args = arguments;
    clearTimeout(timer);
    timer = setTimeout(function () {
      callback.apply(context, args);
    }, ms || 0);
  };
}

