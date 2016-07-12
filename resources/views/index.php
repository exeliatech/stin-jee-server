<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stin Jee App</title>
    <link href="/css/style.css" media="all" rel="stylesheet" type="text/css"/>
    <script data-main="js/app/public/main" src="https://cdnjs.cloudflare.com/ajax/libs/require.js/2.2.0/require.min.js"></script>

    <script type="text/template" id="StartTemplate">
        <div class="start">
            <div class="start_title">
                <%= locale.get('enter_batch') %>
            </div>
            <div class="input">
                <input type="text" id="batch_id" />
                <!--[if (!IE)|(gt IE 8)]> -->
                <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjQsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMzZweCIgaGVpZ2h0PSIzNnB4IiB2aWV3Qm94PSIwIDAgMzYgMzYiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDM2IDM2IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxjaXJjbGUgZmlsbD0iIzc1QUQ0NSIgY3g9IjE3LjMzMyIgY3k9IjE3LjMzNCIgcj0iMTcuMzMzIi8+DQo8cG9seWdvbiBmaWxsPSIjRkZGRkZGIiBwb2ludHM9IjE3LjMwMywyNC4wNjIgMTguMzkxLDI1LjE0OCAyNi4yMDUsMTcuMzM0IDE4LjM5MSw5LjUyIDE3LjMwMywxMC42MDYgMjMuMjYyLDE2LjU2NCA4LjQ2MywxNi41NjQgDQoJOC40NjMsMTguMTAzIDIzLjI2MiwxOC4xMDMgIi8+DQo8L3N2Zz4NCg==" id="batch_confirm" alt="" />
                <!-- <![endif]-->
                <!--[if lte IE 9]>
                <img src="/i/png/icon-arrow.png" id="batch_confirm" alt="" />
                <![endif]-->
            </div>
            <br/>
            <div class="bulk_upload_text">
                <%= locale.get('bulk_upload_text') %> <a href="mailto:<%= locale.get('support_email') %>?subject=<%= locale.get('bulk_upload_mail_subject').replace(new RegExp('\\s','g'), '%20') %>"><%= locale.get('support_email') %></a>
            </div>
            <div class="start_footer">
                <%= locale.get('main_screen_info') %>
            </div>
        </div>
    </script>

    <script type="text/template" id="BatchNotFoundTemplate">
        <div class="error">
            <div class="error_text">
                <%= locale.get('batch_not_found', [typeof invalid_batch_id !== 'undefined'? invalid_batch_id: '----']) %>
            </div>
            <a href="#!/" class="button" style="margin-bottom: 0px;"><%= locale.get('go_back') %></a>
        </div>
    </script>

    <script type="text/template" id="HeaderTemplate">
        <% if ( state !== 'new_specials' && state !== 'batch_not_found') { %>
        <% if ( (typeof batch != 'undefined' && batch != null) && state !== 'start') { %>
        <!--[if (!IE)|(gt IE 8)]> -->
        <a href="#!/new_specials" class="new_special_button">
            <!-- <![endif]-->
            <!--[if lte IE 9]>
            <a href="#!/new_specials" class="new_special_button" style="background: #75ad45 url(/i/png/plus_sm.png) 8px 7px no-repeat;">
            <![endif]-->
            <span><%= locale.get('add_new_special') %></span>
            <%= locale.get('you_have_x_more_tokens', [(typeof batch === 'undefined' || typeof batch.get === 'undefined' || typeof batch.get('specialsNum') === 'undefined' || typeof batch.get('tokensNum') === 'undefined'  || batch.get('specialsNum') >  batch.get('tokensNum')  ? 0 : batch.get('tokensNum') - batch.get('specialsNum'))]) %>
        </a>
        <% } %>

        <!--   <% if (state !== 'get_more_tokens') { %>
               <a href="#!/get_more_tokens" class="new_special_button get_more_tokens" <% if (state === 'start') { %>style="left: 10px;"<% } %>>
                   <span><%= locale.get('get_more_tokens') %></span>
               </a>
           <% } %> -->
        <% } %>
        <!--[if (!IE)|(gt IE 8)]> -->
        <a href="#!/" class="logo" style="background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjQsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMjMxLjYyMnB4IiBoZWlnaHQ9IjQwLjYxN3B4IiB2aWV3Qm94PSIwIDAgMjMxLjYyMiA0MC42MTciIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDIzMS42MjIgNDAuNjE3Ig0KCSB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxnPg0KCQk8cGF0aCBmaWxsPSIjNDc2OTJBIiBkPSJNOTQuNDU2LDE0LjIxM2MwLTAuMjA5LTAuMDAzLTAuNDI0LTAuMDEzLTAuNjQxcy0wLjAxNC0wLjQzMi0wLjAxNC0wLjY0MXMwLTAuMzgxLDAtMC41MjFoLTQuNDkxdjEuMTc2DQoJCQl2MS4xMjFoLTAuMzY2SDgxLjI0djUuMDY4YzAsMC4wNTMsMC4wMDUsMC4xMDQsMC4wMTUsMC4xNTZjMC4wMDgsMC4wNTMsMC4wMTMsMC4xMDQsMC4wMTMsMC4xNTYNCgkJCWMtMC4wMTksMC4xNDEtMC4wMjgsMC4yNzktMC4wMjgsMC40MTh2MC40NDVjMCwwLjY3OCwwLjE0NSwxLjI3OSwwLjQzMiwxLjgwMWMwLjI4OCwwLjUyMywwLjc3OSwwLjc4MywxLjQ3NywwLjc4M2gwLjkzOWgxLjMzMg0KCQkJaDMuNDc0aDcuOTM5YzAuODE5LDAsMS4zNDgsMC4xMDUsMS41OTQsMC4zMTJjMC4yNDIsMC4yMDksMC4zNjYsMC41MDIsMC4zNjYsMC44ODN2MC40Njl2Mi4xMzFjMCwwLjA4OCwwLDAuMjE3LDAsMC4zODkNCgkJCWMwLDAuMTc2LTAuMDM2LDAuMzQ4LTAuMTA2LDAuNTIxYy0wLjA2OSwwLjE3NC0wLjE5MSwwLjMyNi0wLjM2NSwwLjQ1NWMtMC4xNzQsMC4xMjktMC40MzUsMC4xOTUtMC43ODQsMC4xOTVsLTExLjEyNS0wLjAyNw0KCQkJYy0wLjQxOCwwLTAuNzU0LTAuMDE0LTEuMDA2LTAuMDM5Yy0wLjI1Mi0wLjAyNS0wLjQ0OC0wLjEtMC41ODgtMC4yMjNjLTAuMTM5LTAuMTIxLTAuMjMxLTAuMzA1LTAuMjczLTAuNTQ3DQoJCQljLTAuMDQ0LTAuMjQ0LTAuMDY2LTAuNTg0LTAuMDY2LTEuMDJMODIuNzMsMjcuMDFjLTAuMzEzLDAtMC41NjctMC4wMDQtMC43NTgtMC4wMTRjLTAuMTkxLTAuMDA4LTAuMzM4LDAuMDE0LTAuNDQzLDAuMDY2DQoJCQljLTAuMTA1LDAuMDUzLTAuMTc4LDAuMTU2LTAuMjIzLDAuMzEyYy0wLjA0MywwLjE1Ni0wLjA2NiwwLjM5MS0wLjA2NiwwLjcwNXYyLjQzYzAsMC40ODYsMC4wMDUsMC44OTYsMC4wMTUsMS4yMjcNCgkJCWMwLjAwOCwwLjMzMiwwLjA0NiwwLjQ5NiwwLjExNiwwLjQ5NmwxNi44NzMsMC4wMjdjMS41MTUsMCwyLjcxNS0wLjQwOCwzLjYwNC0xLjIyN2MwLjk0LTAuODMyLDEuNDEtMS45OTgsMS40MS0zLjQ5di03LjQ1NQ0KCQkJbC0xNy42MDMtMC4wMjVjLTAuMjk1LDAtMC40NzktMC4xMzktMC41NDgtMC40MThjLTAuMDY5LTAuMjc3LTAuMTA0LTAuNTg0LTAuMTA0LTAuOTE0di0wLjUyM2gxOC4yNTR2LTMuNWgtOC44MDENCgkJCUM5NC40NTYsMTQuNTg2LDk0LjQ1NiwxNC40MjIsOTQuNDU2LDE0LjIxM3oiLz4NCgkJPHBhdGggZmlsbD0iIzQ3NjkyQSIgZD0iTTEyMi4xNjcsMzAuODQ4Ii8+DQoJCTxwYXRoIGZpbGw9IiM0NzY5MkEiIGQ9Ik0xMTYuODkyLDI3Ljc0di0zLjk0M3YtNS42NDFoNS45Mjh2LTMuNTc4aC01Ljg1MmMtMC4wMTYtMC4xOTEtMC4wMjQtMC40MTgtMC4wMjQtMC42OA0KCQkJYzAtMC4yNiwwLTAuNDc5LDAtMC42NTJjMC0wLjEzOSwwLTAuMzYxLDAtMC42NjZzMC4wMDktMC41MzUsMC4wMjQtMC42OTFoLTIuMzc1aC0yLjUzM2MtMC4wMTcsMC4wODYtMC4wMjMsMC4zMDktMC4wMTMsMC42NjYNCgkJCWMwLjAwOCwwLjM1NywwLjAxMywwLjcyNywwLjAxMywxLjEwOWMwLDAuMTc0LTAuMDA5LDAuMzU3LTAuMDI1LDAuNTQ5Yy0wLjAxOSwwLjE4OS0wLjA1MywwLjMyMi0wLjEwNiwwLjM5MWgtNS41Mzd2My41NTNoNy4yNjENCgkJCWMtMC4xNTYsMC40MzYtMC4yOTcsMC43MTMtMC40MTgsMC44MzZjLTAuMTkxLDAuMTM5LTAuMzgzLDAuMjctMC41NzQsMC4zOTNjLTAuMTkxLDAuMTIxLTAuMzgyLDAuMjUyLTAuNTc1LDAuMzkxdjEwLjAyOQ0KCQkJYzAsMC45MzktMC42MjYsMi4xMzEtMS44OCwzLjU3NmwzLjE4OCwzLjYzMWMxLjE2Ni0wLjgxOCwyLjI0NS0yLjcxNywzLjIzOC01LjY5M2MwLjEwNC0wLjM0OCwwLjE5Ni0wLjY0MywwLjI3My0wLjg4OSIvPg0KCQk8cmVjdCB4PSIxMjQuNDExIiB5PSI5LjExOSIgZmlsbD0iIzQ3NjkyQSIgd2lkdGg9IjQuNDE0IiBoZWlnaHQ9IjQuMDIxIi8+DQoJCTxwYXRoIGZpbGw9IiM0NzY5MkEiIGQ9Ik0xMjQuMzg2LDI3LjQ1M2MwLDEuOTg0LDAuMTMxLDMuMzg3LDAuMzkzLDQuMjA1YzAuMTA0LDAuMzE0LDAuMjk1LDAuNzE5LDAuNTc0LDEuMjE1DQoJCQljMC4yNzcsMC40OTYsMC41NzQsMC45NjcsMC44ODksMS40MWMwLjMxMiwwLjQ0MywwLjYwOSwwLjgwNSwwLjg4NywxLjA4NGMwLjI3OSwwLjI3NywwLjQ2MywwLjM1NywwLjU0OSwwLjIzNGwwLjk5Mi0xLjM1Nw0KCQkJbDEuNjIxLTIuMzI0Yy0wLjM1LTAuMjI3LTAuNjAyLTAuMzY1LTAuNzU4LTAuNDE4Yy0wLjE1OC0wLjA1My0wLjMwNS0wLjIwMS0wLjQ0My0wLjQ0M2MtMC4xNDEtMC4yNzktMC4yMTEtMS40OTgtMC4yMTEtMy42NTgNCgkJCWMwLTEuMDc4LDAuMDA4LTIuMzkzLDAuMDI3LTMuOTQzYzAuMDE4LTEuNTQ5LDAuMDI1LTMuMzUyLDAuMDI1LTUuNDA0YzAuMTkxLDAsMC40NDcsMC4wMDQsMC43NzEsMC4wMTINCgkJCWMwLjMyLDAuMDA4LDAuNjk5LDAuMDE0LDEuMTM1LDAuMDE0YzAuNjk3LDAsMS4wNy0wLjAyNSwxLjEyMy0wLjA3OHYtMy4zNDRoLTcuNTc0VjI3LjQ1M3oiLz4NCgkJPHBhdGggZmlsbD0iIzQ3NjkyQSIgZD0iTTE1MC42NjEsMTguMTU2bC0wLjU2Mi0xLjUwMmMtMC4xMjktMC4zMDUtMC4yOTEtMC42MDUtMC40ODItMC45Yy0wLjE4OS0wLjI5Ny0wLjQyLTAuNTUzLTAuNjg5LTAuNzcxDQoJCQlzLTAuNTk2LTAuMzI2LTAuOTc5LTAuMzI2aC0xMi44NTR2My40NjloMi4xNDF2MTEuMjg3aC0yLjExN3YyLjg0OGg2LjE2NlYxOC4wNzhoNC4xMjdjMC41OSwwLDAuOTQ3LDAuMDc4LDEuMDcsMC4yMzQNCgkJCWMwLjEwNCwwLjIyNSwwLjI3MywwLjY0NiwwLjUxLDEuMjY0YzAuMjM0LDAuNjE3LDAuNTI1LDEuNDMsMC44NzMsMi40MzhjMC4zNSwwLjk3MywwLjY4NCwxLjk0MywxLjAwOCwyLjkwOA0KCQkJYzAuMzIsMC45NjUsMC42NDgsMS45MjQsMC45NzksMi44ODFsMS41NDEsNC40M2gyLjg0OGMwLjI5NSwwLDAuNTgyLDAsMC44NjEsMGMwLjI3NywwLDAuNDM0LDAuMDEsMC40NzEsMC4wMjdsLTAuNjgtMS42Nw0KCQkJTDE1MC42NjEsMTguMTU2eiIvPg0KCQk8cGF0aCBmaWxsPSIjNDc2OTJBIiBkPSJNMTc0LjQ3OCwzMS4wMzFjMC4wMTgsMC4wMzUsMC4wMjcsMC4yMTMsMC4wMjcsMC41MzVjMCwwLjMyNCwwLDAuNTk4LDAsMC44MjQNCgkJCWMwLDEuMDA4LTAuMjQ2LDEuOTg2LTAuNzMyLDIuOTM4Yy0wLjQ4OCwwLjk0OS0xLjE1OCwxLjY2Ni0yLjAxLDIuMTU0YzAuMTU2LDAuMjYsMC4zMTYsMC41MjMsMC40ODIsMC43ODMNCgkJCWMwLjE2NiwwLjI2MiwwLjMzNiwwLjUyMywwLjUxLDAuNzgzYzAuNDY5LDAuNzMyLDAuNzY0LDEuMjU0LDAuODg3LDEuNTY4YzAuMjQ0LDAsMC41NjItMC4xNDgsMC45NTMtMC40NDMNCgkJCWMwLjM5My0wLjI5OSwwLjc4NS0wLjY0NSwxLjE3OC0xLjA0N2MwLjM5MS0wLjQsMC43NDgtMC44MDksMS4wNjgtMS4yMjdjMC4zMjQtMC40MTgsMC41MzctMC43NSwwLjY0MS0wLjk5Mg0KCQkJYzAuMjQ0LTAuNjI3LDAuNDQzLTEuMTU0LDAuNjAyLTEuNThjMC4xNTYtMC40MjgsMC4yODEtMC44MDcsMC4zNzktMS4xMzdjMC4wOTQtMC4zMywwLjE2Mi0wLjY0NSwwLjE5NS0wLjkzOQ0KCQkJYzAuMDM1LTAuMjk3LDAuMDUxLTAuNjA5LDAuMDUxLTAuOTQxVjE4LjEyOWgxLjA0NWgyLjA5di0wLjc2NHYtMi41NzhoLTcuMzY1VjMxLjAzMXoiLz4NCgkJPHJlY3QgeD0iMTc0LjI5NCIgeT0iOS41NjIiIGZpbGw9IiM0NzY5MkEiIHdpZHRoPSI0LjM2MyIgaGVpZ2h0PSIzLjg2NSIvPg0KCQk8cGF0aCBmaWxsPSIjNDc2OTJBIiBkPSJNMTg2LjU2OSwxOS44NzloLTEuNTkydjguMTEzYzAsMC42NDYsMC4wNTMsMS4xNTIsMC4xNTYsMS41MThjMC4xNTYsMC40NTUsMC40LDAuODU3LDAuNzMsMS4yMDUNCgkJCWMwLjMzMiwwLjM1LDAuNzEzLDAuNjM3LDEuMTUsMC44NjVjMC40MzYsMC4yMjcsMC44OTUsMC4zOTUsMS4zODUsMC41MWMwLjQ4NiwwLjExMywwLjk1NywwLjE3LDEuNDEsMC4xN2gxNi43MTV2LTUuMTQ1aC0zLjE4OA0KCQkJYzAsMS4zMjItMC4zNjUsMS45ODItMS4wOTYsMS45ODJoLTExLjMzNmMtMC42NzgsMC0xLjEwNS0wLjIzNC0xLjI3Ny0wLjcwN2MtMC4xNzYtMC40NzMtMC4yNjItMS4xMzctMC4yNjItMS45OTJ2LTMuMjU0aDE3LjM2Nw0KCQkJdi0zLjI2NmgtMTYuNzY4SDE4Ni41Njl6Ii8+DQoJCTxwb2x5Z29uIGZpbGw9IiM0NzY5MkEiIHBvaW50cz0iMTk4LjE2NywxMi4yNTQgMTkzLjcyOCwxMi4yNTQgMTkzLjcyOCwxNC42MDQgMTg1LjA1NiwxNC42MDQgMTg1LjA1NiwxNy44OTUgMjA2LjczMywxNy44OTUgDQoJCQkyMDYuNzMzLDE0LjU2MiAxOTguMTY3LDE0LjU2MiAJCSIvPg0KCQk8cG9seWdvbiBmaWxsPSIjNDc2OTJBIiBwb2ludHM9IjIyMy4wNTYsMTQuNTYyIDIyMy4wNTYsMTIuMjU0IDIxOC42MTYsMTIuMjU0IDIxOC42MTYsMTQuNjA0IDIwOS45NDQsMTQuNjA0IDIwOS45NDQsMTcuODk1IA0KCQkJMjMxLjYyMiwxNy44OTUgMjMxLjYyMiwxNC41NjIgCQkiLz4NCgkJPHBhdGggZmlsbD0iIzQ3NjkyQSIgZD0iTTIxMS40NiwxOS44NzloLTEuNTk0djguMTEzYzAsMC42NDYsMC4wNTMsMS4xNTIsMC4xNTYsMS41MThjMC4xNTgsMC40NTUsMC40LDAuODU3LDAuNzMyLDEuMjA1DQoJCQljMC4zMywwLjM1LDAuNzEzLDAuNjM3LDEuMTQ4LDAuODY1YzAuNDM2LDAuMjI3LDAuODk2LDAuMzk1LDEuMzg1LDAuNTFjMC40ODYsMC4xMTMsMC45NTcsMC4xNywxLjQxLDAuMTdoMTYuNzE1di01LjE0NWgtMy4xODYNCgkJCWMwLDEuMzIyLTAuMzY3LDEuOTgyLTEuMDk4LDEuOTgyaC0xMS4zMzRjLTAuNjgsMC0xLjEwNy0wLjIzNC0xLjI3OS0wLjcwN2MtMC4xNzYtMC40NzMtMC4yNjItMS4xMzctMC4yNjItMS45OTJ2LTMuMjU0aDE3LjM2Nw0KCQkJdi0zLjI2NmgtMTYuNzY4SDIxMS40NnoiLz4NCgk8L2c+DQoJPGc+DQoJCTxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBmaWxsPSIjNDc2OTJBIiBkPSJNMjMuNzE4LDIzLjMyMmMtMC4xMTcsMC4wNTEtMC4yNzgsMC4yOTMtMC4yNjUsMC4zMDcNCgkJCWMwLjE2LDAuMTc2LDAuMzI0LDAuMzg1LDAuNTMzLDAuNDczYzEuNDMyLDAuNTkyLDIuODc1LDEuMTU2LDQuMzExLDEuNzI5QzI4LjA0NiwyNC4zOTgsMjUuMTE2LDIyLjcwNywyMy43MTgsMjMuMzIyeiIvPg0KCQk8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZmlsbD0iIzQ3NjkyQSIgZD0iTTI3Ljg5NiwyMC41MjVjLTAuNjIyLTAuMDMxLTEuNDc4LDAuMTEzLTEuODU1LDAuNTI1DQoJCQlsMi4xNDksMC41NTVDMjguNjksMjEuMDc0LDI4LjU0NywyMC41NTksMjcuODk2LDIwLjUyNXoiLz4NCgkJPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGZpbGw9IiM0NzY5MkEiIGQ9Ik0yOC40MDUsMjkuMjY0YzAuMDMyLDAuMzc1LDAuNDg5LDAuNzE3LDAuNzU5LDEuMDcyDQoJCQljMC4xNjctMS42NzgsMC43MzQtMi4xNTgsMi4yMDctMS43MDNjMC43MzIsMC4yMjcsMS4zODcsMC43MDUsMi4xNTEsMS4xMDljMC4zNjYtMC41OTIsMC4yNDItMS4yMDMtMC40MTMtMS41NzgNCgkJCWMtMS4zNDYtMC43Ny0yLjc5Mi0wLjg2My00LjE3LTAuMTM1QzI4LjYxNSwyOC4xOTksMjguMzcsMjguODU3LDI4LjQwNSwyOS4yNjR6Ii8+DQoJCTxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBmaWxsPSIjNDc2OTJBIiBkPSJNMjQuOTAzLDMyLjY0NmMwLDAsNS4wNDksMi44NDIsNi42ODgsMi44NDINCgkJCWMxLjcyNCwwLDYuNzYzLTIuODQyLDYuNzYzLTIuODQycy01LjA4LDAuNjA1LTYuNzYzLDAuNjA1QzI5LjkxMSwzMy4yNTIsMjQuOTAzLDMyLjY0NiwyNC45MDMsMzIuNjQ2eiIvPg0KCQk8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZmlsbD0iIzQ3NjkyQSIgZD0iTTY3LjczNSwxNy44OTZjLTAuNTcyLTAuNTI1LTEuMjQ1LTAuOTczLTEuOTM4LTEuMzMNCgkJCWMtMi41NDUtMS4zMTItNS4xMDctMi41NDUtNy42NTMtMy44NTdMMzcuNDU1LDBjMCwwLTEuMTUyLDAtMS43MDUsMGMtMS4wOTIsMC43MjctMi4xOSwxLjEtMy4yNywxLjY4NmwtOC4zNzUsNC4zODENCgkJCWMtMS44MzcsMC45NTctMy42NDksMS45NTktNS40ODMsMi45MjJjLTUuNTA4LDIuODg1LTExLjAzMSw1Ljc0LTE2LjUyMSw4LjY1NkMxLjM2MSwxOC4wMzcsMC43NzYsMTguNzE5LDAsMTkuMzY1DQoJCQljMS44NTgsMS4yODMsMy42MDMsMS45NTksNS40MzgsMi4xMDRjMy4yOTQsMC4yNiw2LjYwMywwLjMxMiw5LjkwOCwwLjQxYzAuNzkxLDAuMDIzLDEuMzI3LDAuMTY0LDEuMjEsMS4wNjYNCgkJCWMtMS4wMSwwLjM3MS0yLjA0NCwwLjY0My0yLjk3NCwxLjExOWMtMS4zNDQsMC42ODgtMi4wOSwyLjQwNi0xLjU2NSwzLjQ1M2MwLjgyLDEuNjMxLDEuOTQzLDIuOTUzLDMuODE3LDMuNDU5DQoJCQljMC4zMjMsMC4wODgsMC43NTMsMC4zNjEsMC44MzksMC42MzljMC40ODMsMS41NDEsMS41NjIsMi41NzgsMi44NzUsMy4zMjJjMS45OTcsMS4xMzMtMC44MDYsMy44NzktMC44MDYsMy44NzkNCgkJCXMxMC4yMywxLjAzNSwxMy4wNTksMS41MzdsMTIuNDU0LTEuMTc4Yy0xLjE5NC0yLjY1NC0wLjU2NS01LjI0MiwxLjM2OC03LjE0NWMwLjI2NC0wLjI2LDAuNTgtMC40NzcsMC44OTUtMC42NzgNCgkJCWMxLjQwOC0wLjg5OCwyLjkzMS0xLjY1Niw0LjIxLTIuNzExYzEuMzY0LTEuMTIzLDEuNTA1LTIuODI0LDAuNDc4LTQuNTgyYy0wLjY4Ny0xLjE3OC0xLjk0My0xLjEzMy0zLjE1Mi0wLjkxDQoJCQljLTAuMTQyLDAuMDI3LTAuMjkyLDAuMDA0LTAuNDc2LDAuMDA0Yy0wLjAxMS0wLjk5OCwwLjYxMi0wLjk2OSwxLjI3LTAuOTk4YzQuNjAzLTAuMjA3LDkuMjA5LTAuMzQ4LDEzLjgtMC43MDcNCgkJCWMxLjY0My0wLjEzMSwzLjMwNy0wLjY1LDQuODQ0LTEuMjc3QzY4LjYxNSwxOS43MTMsNjguNjQxLDE4LjcyNyw2Ny43MzUsMTcuODk2eiBNMzYuMTI1LDIuMTdsMjMuODgxLDEzLjA2NA0KCQkJYzAsMC0yMC40MTktMS45OTQtMjYuNzIyLTEuOTk0Yy02LjEwNiwwLTIyLjkxNCwxLjk5NC0yMi45MTQsMS45OTRMMzYuMTI1LDIuMTd6IE0xNi4wODgsMjAuOTE4DQoJCQljLTQuNDEsMC4wMzEtOC43OS0wLjIyMy0xMy4wNDYtMS40MmMtMC4zOTQtMC4xMDktMC43NDktMC4zMzYtMS4zMTUtMC41OThjNS4yMzktMS45LDEwLjUyNC0yLjMxOCwxNi4wNDQtMi42ODkNCgkJCWMtMC4zNDksMS40ODgtMC42MjEsMi43NzktMC45NzYsNC4wNTFDMTYuNzIsMjAuNTI5LDE2LjMzMywyMC45MTYsMTYuMDg4LDIwLjkxOHogTTQ4LjQwOCwyMy44OTENCgkJCWMwLjUzNC0wLjA0MSwxLjIzOSwwLjMyLDEuNjQxLDAuNzI3YzEuMDk1LDEuMTA1LDAuNzgsMy4yMDUtMC41OTksNC4xMDRjLTAuOTg4LDAuNjQ2LTIuMDgzLDEuMTI1LTMuMSwxLjcyOQ0KCQkJYy0wLjI4MywwLjE2OC0wLjQ3NiwwLjUwNC0wLjY4NiwwLjc3OWMtMy4xNSw0LjE2LTcuMjE4LDYuOTM2LTEyLjQ4Niw3LjE2NmMtMi4xNzksMC4wOTYtNC41MTMtMC42Ni02LjU4NC0xLjUNCgkJCWMtMi4zNDQtMC45NTMtNC41MzYtMi4zNC02LjY3NS0zLjcxOWMtMS4yNDYtMC44MDEtMi44NDgtMS41NDUtMi40Ny0zLjcwN2MtMC43NDEsMS4xMDctMS40ODEsMC42NjItMi4wNzcsMC4xNzgNCgkJCWMtMC42NTgtMC41MzctMS40Mi0xLjE0NS0xLjcwNS0xLjg4N2MtMC4zMDktMC44MDUtMC40NzktMi4wNTctMC4wNTUtMi42NDFjMC40NzQtMC42NTYsMS42NjUtMC44MzYsMi41ODEtMS4wOTINCgkJCWMwLjE4OC0wLjA1NSwwLjY2MiwwLjQxOCwwLjc4MiwwLjczNGMwLjE5OCwwLjUxNiwwLjIxNCwxLjEsMC40NzUsMS42NjZjMS4wNTMtMS41NTcsMS4xMDctMy4zNjksMS40MjYtNS4wOA0KCQkJYzAuMzMxLTEuNzczLDAuNDA3LTMuNTk2LDAuNjI4LTUuNzEzYzguNjAxLTAuMjg5LDE3LjMyMy0wLjAzNywyNi4wNzYtMC4zMzJjMC4yNTksMy42MjEsMC40NTcsNi4zNjMsMC42ODYsOS41NTcNCgkJCUM0Ny4xNDQsMjQuNDM4LDQ3Ljc1OSwyMy45NDEsNDguNDA4LDIzLjg5MXogTTYzLjA0NCwxOS40OThjLTQuNTA4LDEuMTk3LTkuMTQ4LDEuNDUxLTEzLjgxOSwxLjQyDQoJCQljLTAuMjYtMC4wMDItMC42NjgtMC4zODktMC43NDgtMC42NTZjLTAuMzc3LTEuMjcxLTAuNjY1LTIuNTYyLTEuMDM0LTQuMDUxQzUzLjI4OSwxNi41ODIsNTguODg4LDE3LDY0LjQzOCwxOC45DQoJCQlDNjMuODM4LDE5LjE2Miw2My40NjEsMTkuMzg5LDYzLjA0NCwxOS40OTh6Ii8+DQoJCTxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBmaWxsPSIjNDc2OTJBIiBkPSJNMzguODEsMjMuODk4Yy0yLjA1MiwwLjQyNC0zLjIyNCwxLjE5Ny0zLjU0MiwyLjYwNw0KCQkJYzAuODM2LTAuNDEyLDEuNTQxLTAuODIsMi4yODgtMS4xMTNjMC43ODUtMC4zMTEsMS43NS0wLjMyOCwyLjQ4My0wLjg4M0M0MC4zNjMsMjQuMjY2LDM5LjU3NCwyMy43NCwzOC44MSwyMy44OTh6Ii8+DQoJCTxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBmaWxsPSIjNDc2OTJBIiBkPSJNMTQuNDI4LDI2LjQ5NGMwLjMxMiwwLjU3LDAuNjI0LDEuMTQzLDEuMDIyLDEuODc1DQoJCQljMC4yNjUtMC44NCwwLjQ0NS0xLjQxNCwwLjY2OC0yLjEyNWMtMC4zNDQtMC4wMTgtMC44ODMtMC4wNDctMS40MjQtMC4wNzJDMTQuNjA1LDI2LjI3NywxNC41MTYsMjYuMzg3LDE0LjQyOCwyNi40OTR6Ii8+DQoJCTxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBmaWxsPSIjNDc2OTJBIiBkPSJNMzguMzE1LDIxLjM2OWMtMC4zNzctMC40MS0xLjIzMi0wLjU1NS0xLjg1NC0wLjUyNQ0KCQkJYy0wLjY1LDAuMDMzLTAuNzk1LDAuNTQ5LTAuMjk1LDEuMDgyTDM4LjMxNSwyMS4zNjl6Ii8+DQoJCTxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBmaWxsPSIjNDc2OTJBIiBkPSJNNDkuMTUxLDI2LjM5M2MtMC4xLTAuMDk4LTAuMi0wLjE5NS0wLjMwMi0wLjI5MQ0KCQkJYy0wLjUzMywwLjA4OC0xLjA2NywwLjE3Ni0xLjQwNiwwLjIzMmMwLjMwMiwwLjY4MiwwLjU0NSwxLjIzMiwwLjkwMSwyLjAzN0M0OC42NTksMjcuNiw0OC45MDUsMjYuOTk2LDQ5LjE1MSwyNi4zOTN6Ii8+DQoJPC9nPg0KPC9nPg0KPC9zdmc+DQo=) center no-repeat;"></a>
        <!-- <![endif]-->
        <!--[if lte IE 9]>
        <a href="#!/" class="logo"><img src="/i/png/logo.png" alt="" style="width: 232px;height: 40px;"/></a>
        <![endif]-->
        <span class="slogan">
                <%= locale.get('eat_drink_for_less') %>
            </span>

        <div class="language_selector">
            <%= locale.get('language') %>: <span><%= locale.get(locale.getCurrent()) %></span>
            <ul>
                <%
                _.each(locale.getList(), function(item, key, list) {
                %>
                <% if (item != locale.getCurrent()) { %>
                <li data-value="<%= item %>"><%= locale.get(item) %></li>
                <% } %>
                <%
                });
                %>
            </ul>
        </div>

        <a href="http://stinjee.com/help" class="help" target="_blank">
            <img src="/i/sj_help.svg" alt="" />
            <%= locale.get('help') %>
        </a>
    </script>

    <script type="text/template" id="SpecialsListTemplate">
        <div class="list">
            <div class="menu menu_left">
                <a href="#" onclick="return false" class="black active" style="cursor: default"><%= locale.get('specials') %></a>
            </div>
            <div class="menu menu_right">
                <a href="#!/specials_list/0" class="yellow"><%= locale.get('all') %></a>
                <a href="#!/specials_list/1"><%= locale.get('active') %></a>
                <a href="#!/specials_list/2" class="blue"><%= locale.get('queued') %></a>
                <a href="#!/specials_list/3" class="grey"><%= locale.get('declined') %></a>
                <a href="#!/specials_list/4" class="red"><%= locale.get('expired') %></a>
            </div>
            <div class="specials_list specials_list_all">
                <%
                if (batch.get('specials').all.length) {
                _.each(batch.get('specials').all, function(item, key, list) {
                %>

                <div class="specials_item" id="<%= item.id  %>" >
                    <div class="specials_name">
                        <% if (typeof item.image !== 'undefined' && item.image) { %>
                        <img src="<%= item.image %>" />
                        <% } %>
                        <span class="name-wrapper"><%- _.truncate(item.name,22)  %></span>
                    </div>
                    <div class="separator"></div>
                    <div class="specials_store_name">
                        <% if (item.store_name) { %>
                        <%= _.truncate(item.store_name, 11) %>
                        <% } else { %>
                        <%= locale.get('unknown_store') %>
                        <% } %>
                    </div>
                    <div class="separator"></div>
                    <div class="specials_website">
                        <% if (item.website) { %>
                        <a href="<%= item.website %>" target="_blank"><%= _.truncate(item.website, 24)  %></a>
                        <% } else { %>
                        <%= locale.get('no_website') %>
                        <% } %>
                    </div>
                    <div class="separator"></div>
                    <div class="specials_date <% if (new Date().getTime() > moment(item.endsAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').valueOf()) {%> specials_expired<% } %>"><%= moment(item.createdAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').format('D MMMM YYYY') %></div>
                    <div class="specials_full_info">
                    </div>
                </div>

                <%
                });
                if (batch.get('specials').all.length >= 20) { %><button class="button load_more" id="specials_all_load_more"><%= locale.get('load_more') %></button><% }
                } else {
                %>
                <div class="no_specials"><%= locale.get('no_specials') %></div>
                <%
                }
                %>
            </div>

            <div class="specials_list specials_list_active">
                <%
                if (batch.get('specials').active.length) {
                _.each(batch.get('specials').active, function(item, key, list) {
                if (item.status != 1) return;
                %>

                <div class="specials_item" id="<%= item.id  %>">
                    <div class="specials_name">
                        <% if (typeof item.image !== 'undefined' && item.image) { %>
                        <img src="<%= item.image %>" />
                        <% } %>
                        <span class="name-wrapper"><%- _.truncate(item.name,22)  %></span>
                    </div>

                    <div class="separator"></div>

                    <div class="specials_store_name">
                        <% if (item.store_name) { %>
                        <%= _.truncate(item.store_name, 11) %>
                        <% } else { %>
                        <%= locale.get('unknown_store') %>
                        <% } %>
                    </div>

                    <div class="separator"></div>

                    <div class="specials_website">
                        <% if (item.website) { %>
                        <a href="<%= item.website %>" target="_blank"><%= _.truncate(item.website, 24)  %></a>
                        <% } else { %>
                        <%= locale.get('no_website') %>
                        <% } %>
                    </div>
                    <div class="separator"></div>
                    <div class="specials_date <% if (new Date().getTime() > moment(item.endsAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').valueOf()) {%> specials_expired<% } %>"><%= moment(item.createdAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').format('D MMMM YYYY') %></div>
                    <div class="specials_full_info">
                    </div>
                </div>
                <%
                });

                if (batch.get('specials').active.length >= 20) { %><button class="button load_more" id="specials_active_load_more"><%= locale.get('load_more') %></button><% }
                } else {
                %>
                <div class="no_specials"><%= locale.get('no_active_specials') %></div>
                <%
                }
                %>
            </div>

            <div class="specials_list specials_list_queued">
                <%
                if (batch.get('specials').queued.length) {
                _.each(batch.get('specials').queued, function(item, key, list) {
                if (item.status != 0) return;
                %>

                <div class="specials_item" id="<%= item.id  %>">
                    <div class="specials_name">
                        <% if (typeof item.image !== 'undefined' && item.image) { %>
                        <img src="<%= item.image %>" />
                        <% } %>
                        <span class="name-wrapper"><%- _.truncate(item.name,22)  %></span>
                    </div>

                    <div class="separator"></div>

                    <div class="specials_store_name">
                        <% if (item.store_name) { %>
                        <%= _.truncate(item.store_name, 11) %>
                        <% } else { %>
                        <%= locale.get('unknown_store') %>
                        <% } %>
                    </div>

                    <div class="separator"></div>

                    <div class="specials_website">
                        <% if (item.website) { %>
                        <a href="<%= item.website %>" target="_blank"><%= _.truncate(item.website, 24)  %></a>
                        <% } else { %>
                        <%= locale.get('no_website') %>
                        <% } %>
                    </div>
                    <div class="separator"></div>
                    <div class="specials_date <% if (new Date().getTime() > moment(item.endsAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').valueOf()) {%> specials_expired<% } %>"><%= moment(item.createdAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').format('D MMMM YYYY') %></div>
                    <div class="specials_full_info">
                    </div>
                </div>
                <%

                });

                if (batch.get('specials').queued.length >= 20) { %><button class="button load_more" id="specials_queued_load_more"><%= locale.get('load_more') %></button><% }
                } else {
                %>
                <div class="no_specials"><%= locale.get('no_queued_specials') %></div>
                <%
                }
                %>
            </div>

            <div class="specials_list specials_list_declined">
                <%
                if (batch.get('specials').declined.length) {
                _.each(batch.get('specials').declined, function(item, key, list) {
                if (item.status != 2) return;
                %>

                <div class="specials_item" id="<%= item.id  %>" >
                    <div class="specials_name">
                        <% if (typeof item.image !== 'undefined' && item.image) { %>
                        <img src="<%= item.image %>" />
                        <% } %>
                        <span class="name-wrapper"><%- _.truncate(item.name,22)  %></span>
                    </div>

                    <div class="separator"></div>

                    <div class="specials_store_name">
                        <% if (item.store_name) { %>
                        <%= _.truncate(item.store_name, 11) %>
                        <% } else { %>
                        <%= locale.get('unknown_store') %>
                        <% } %>
                    </div>

                    <div class="separator"></div>

                    <div class="specials_website">
                        <% if (item.website) { %>
                        <a href="<%= item.website %>" target="_blank"><%= _.truncate(item.website, 24)  %></a>
                        <% } else { %>
                        <%= locale.get('no_website') %>
                        <% } %>
                    </div>
                    <div class="separator"></div>
                    <div class="specials_date <% if (new Date().getTime() > moment(item.endsAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').valueOf()) {%> specials_expired<% } %>"><%= moment(item.createdAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').format('D MMMM YYYY') %></div>
                    <div class="specials_full_info">
                    </div>
                </div>

                <%
                });
                if (batch.get('specials').declined.length >= 20) { %><button class="button load_more" id="specials_declined_load_more"><%= locale.get('load_more') %></button><% }
                } else {
                %>
                <div class="no_specials"><%= locale.get('no_declined_specials') %></div>
                <%
                }
                %>
            </div>

            <div class="specials_list specials_list_expired">
                <%
                if (batch.get('specials').expired.length) {
                _.each(batch.get('specials').expired, function(item, key, list) {

                %>

                <div class="specials_item" id="<%= item.id  %>" >
                    <div class="specials_name">
                        <% if (typeof item.image !== 'undefined' && item.image) { %>
                        <img src="<%= item.image %>" />
                        <% } %>
                        <span class="name-wrapper"><%- _.truncate(item.name,22)  %></span>
                    </div>

                    <div class="separator"></div>

                    <div class="specials_store_name">
                        <% if (item.store_name) { %>
                        <%= _.truncate(item.store_name, 11) %>
                        <% } else { %>
                        <%= locale.get('unknown_store') %>
                        <% } %>
                    </div>

                    <div class="separator"></div>

                    <div class="specials_website">
                        <% if (item.website) { %>
                        <a href="<%= item.website %>" target="_blank"><%= _.truncate(item.website, 24)  %></a>
                        <% } else { %>
                        <%= locale.get('no_website') %>
                        <% } %>
                    </div>
                    <div class="separator"></div>
                    <div class="specials_date <% if (new Date().getTime() > moment(item.endsAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').valueOf()) {%> specials_expired<% } %>"><%= moment(item.createdAt, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').format('D MMMM YYYY') %></div>
                    <div class="specials_full_info">
                    </div>
                </div>

                <%
                });
                if (batch.get('specials').expired.length >= 20) { %><button class="button load_more" id="specials_expired_load_more"><%= locale.get('load_more') %></button><% }
                } else {
                %>
                <div class="no_specials"><%= locale.get('no_expired_specials') %></div>
                <%
                }
                %>
            </div>
        </div>
    </script>

    <script type="text/template" id="InvalidTokenTemplate">
        <div class="error">
            <div class="error_text">
                Token with ID:<%= invalid_token_id %> not found in Batch <%= batch.id %>.<br/><br/>
                <a href="#!/specials_list" class="button" style="margin-bottom: 0px;">Go back!</a>
            </div>
        </div>
    </script>

    <script type="text/template" id="NewSpecialsTemplate">
        <div class="list new_special">
            <div class="list_header"><%= locale.get('add_new_special') %></div>
            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('name_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_name"><%= locale.get('special_name') %></label>
                <input type="text" id="specials_name" value=""  placeholder="Enter a title" />
            </div>
            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('description_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_description"><%= locale.get('description') %></label>
                <textarea id="specials_description"  placeholder="Enter a description"></textarea>
            </div>
            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('store_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_name"><%= locale.get('store_name') %></label>
                <input type="text" id="specials_store" value="" />

                <div class="map">
                    <div id="map"></div>
                    <div class="foreground"></div>
                </div>
            </div>
            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('address_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_addres"><%= locale.get('address') %></label>
                <input type="text" id="specials_addres" value=""  placeholder="London, England" />
                <input type="hidden" id="specials_country" value="" />
                <input type="hidden" id="specials_country_code" value="" />
            </div>
            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('website_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_website"><%= locale.get('website') %></label>
                <input type="text" id="specials_website" value="" placeholder="http://" />
            </div>
            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('phone_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_phone"><%= locale.get('phone_number') %></label>
                <input type="text" id="specials_phone" value="" placeholder="" />
            </div>
            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('image_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_image"><%= locale.get('image') %></label>
                <input type="file" id="specials_image" value="" />
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('store_logo_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_store_logo"><%= locale.get('store_logo') %></label>

                <div class="image_holder store_logo_holder">
                    <input type="file" id="specials_store_logo" value="" />
                </div>
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('store_logo_bg_field_tooltip') %>
                    <div></div>
                </div>
                <label for="store_logo_bg"><%= locale.get('store_logo_bg') %></label>
                <input type="text" id="store_logo_bg" value="#ffffff"  readonly />
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('admin_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_image"><%= locale.get('let_admin_choose_photo') %></label>
                <input type="checkbox" id="specials_admin_image" value="" />
            </div>
            <div class="field">
                <a href="#" class="q" onclick="var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('urgent_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_image"><%= locale.get('urgent') %></label>
                <input type="checkbox" id="specials_urgent" value="" />
            </div>
            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('valid_for_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_image"><%= locale.get('valid_for_days') %></label>
                <select id="specials_days">
                    <option>7</option>
                    <option>6</option>
                    <option>5</option>
                    <option>4</option>
                    <option>3</option>
                    <option>2</option>
                    <option>1</option>
                </select>
            </div>
            <div class="field" <% if (batch.get('tokensNum') > batch.get('specialsNum')) { %> style="display: none"<% } %>>
            <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
            <div class="tooltip">
                <%= locale.get('batch_field_tooltip') %>
                <div></div>
            </div>
            <label for="specials_token"><%= locale.get('batch_id') %></label>
            <input type="text" id="specials_batch" value="" class="additional" />
        </div>
        <div class="field">
            <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
            <div class="tooltip">
                <%= locale.get('token_field_tooltip') %>
                <div></div>
            </div>
            <label for="specials_token"><%= locale.get('token_id') %></label>
            <input type="text" id="specials_token" value="" class="additional" />
        </div>
        <div class="field_buttons">
            <a href="#!/specials_list/all" class="button"><%= locale.get('cancel') %></a>
            <input type="button" id="specials_preview" class="specials_confirm" value="<%= locale.get('preview_special') %>" />

            <!--<input type="button" id="specials_confirm"  value="<%= locale.get('submit_special_for_approval') %>" />-->
        </div>
        </div>

        <div class="list special_preview">
            <div class="list_header"><%= locale.get('preview_special') %></div>
            <div class="special_preview-content"></div>

            <div class="field_buttons">
                <input type="button" id="edit" class="button" value="<%= locale.get('edit') %>" />
                <input type="button" id="specials_confirm" class="specials_confirm" value="<%= locale.get('submit_special_for_approval') %>" />
            </div>
        </div>
    </script>

    <script type="text/template" id="SpecialSuccessTemplate">
        <div class="error">
            <div class="error_text">
                Specials created successfuly.<br/><br/>
                <a href="#!/specials_list" class="button" style="margin-bottom: 0px;">Go back!</a>
            </div>
        </div>
    </script>

    <script type="text/template" id="SpecialReuseTemplate">
        <div class="list new_special">
            <div class="list_header"><%= locale.get('reuse_special') %></div>
            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('name_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_name"><%= locale.get('special_name') %></label>
                <input type="text" id="specials_name" value="<%= special.name %>"  placeholder="Enter a title" />
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('description_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_description"><%= locale.get('description') %></label>
                <textarea id="specials_description"  placeholder="Enter a description"><%= special.description %></textarea>
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('store_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_name"><%= locale.get('store_name') %></label>
                <input type="text" id="specials_store" value="<%= special.storeName %>" />

                <div class="map">
                    <div id="map"></div>
                    <div class="foreground"></div>
                </div>
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('address_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_addres"><%= locale.get('address') %></label>
                <input type="text" id="specials_addres" value="<%= special.addres %>"  placeholder="London, England" />
                <input type="hidden" id="specials_country" value="<%= special.country %>" />
                <input type="hidden" id="specials_country_code"  value="<%= special.country_code %>" />
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('website_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_website"><%= locale.get('website') %></label>
                <input type="text" id="specials_website" value="<%= special.site %>" placeholder="http://" />
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('phone_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_phone"><%= locale.get('phone_number') %></label>
                <input type="text" id="specials_phone" value="<%= special.phone %>" placeholder="" />
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('image_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_image"><%= locale.get('image') %></label>

                <div class="image_holder">
                    <input type="file" id="specials_image" value="" />

                    <% if (special.image) { %>
                    <div class="image">
                        <img src="<%= special.image %>" />
                        <i onclick="$('.image').remove();">x</i>
                    </div>
                    <% } %>
                </div>

            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('store_logo_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_store_logo"><%= locale.get('store_logo') %></label>

                <div class="image_holder store_logo_holder">
                    <input type="file" id="specials_store_logo" value="" />

                    <% if (special.store_logo) { %>
                    <div class="image_store">
                        <div class="image_wrapper" style="background: <% if (special.store_logo_bg) { %><%= special.store_logo_bg %><% } else { %>#ffffff<% } %>">
                            <span></span>
                            <img src="<%= special.store_logo %>" />
                        </div>
                        <i>x</i>
                    </div>
                    <% } %>
                </div>
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('store_logo_bg_field_tooltip') %>
                    <div></div>
                </div>
                <label for="store_logo_bg"><%= locale.get('store_logo_bg') %></label>
                <input type="text" id="store_logo_bg" value="<% if (special.store_logo_bg) { %><%= special.store_logo_bg %><% } else { %>#ffffff<% } %>"  readonly />
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('admin_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_image"><%= locale.get('let_admin_choose_photo') %></label>
                <input type="checkbox" id="specials_admin_image" value="" <% if (special.letAdminChooseImage) { %> checked <% } %>/>
            </div>

            <div class="field">
                <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
                <div class="tooltip">
                    <%= locale.get('valid_for_field_tooltip') %>
                    <div></div>
                </div>
                <label for="specials_image"><%= locale.get('valid_for_days') %></label>
                <select id="specials_days">
                    <option>7</option>
                    <option <% if (special.validFor === 6) { %> selected <% } %>>6</option>
                    <option <% if (special.validFor === 5) { %> selected <% } %>>5</option>
                    <option <% if (special.validFor === 4) { %> selected <% } %>>4</option>
                    <option <% if (special.validFor === 3) { %> selected <% } %>>3</option>
                    <option <% if (special.validFor === 2) { %> selected <% } %>>2</option>
                    <option <% if (special.validFor === 1) { %> selected <% } %>>1</option>
                </select>
            </div>

            <input type="hidden" id="source_id" value="<%= reuse_id %>" />

            <div class="field" <% if (batch.get('tokensNum') > batch.get('specialsNum')) { %> style="display: none"<% } %>>
            <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
            <div class="tooltip">
                <%= locale.get('batch_field_tooltip') %>
                <div></div>
            </div>
            <label for="specials_token"><%= locale.get('batch_id') %></label>
            <input type="text" id="specials_batch" value="" class="additional" />
        </div>

        <div class="field">
            <a href="#" class="q" onclick=" var el = $(this).parent().find('.tooltip'); if (el.is(':visible')) $('.tooltip').hide(); else { $('.tooltip').hide(); el.show(); } return false; "></a>
            <div class="tooltip">
                <%= locale.get('token_field_tooltip') %>
                <div></div>
            </div>
            <label for="specials_token"><%= locale.get('token_id') %></label>
            <input type="text" id="specials_token" value="" class="additional" />
        </div>

        <div class="field_buttons">
            <a href="#!/specials_list/all" class="button"><%= locale.get('cancel') %></a>
            <input type="button" id="specials_confirm" value="<%= locale.get('submit_special_for_approval') %>" />
        </div>
        </div>
    </script>

    <script type="text/template" id="GetMoreTokensTemplate">
        <div class="bulk_results buy_tokens">

            <div class="list_header">
                <%= locale.get('get_more_tokens') %>
            </div>

            <div id="start_section">
                <div class="bulk_upload_text" style="margin-bottom: 30px;">
                    <%= locale.get('buy_tokens_text') %>
                </div>

                <div class="bulk_upload_text" style="margin-bottom: 30px;">
                    <%= locale.get('buy_tokens_text_2') %>
                </div>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('buy_tokens') %>
                    </label>

                    <select id="tokens" onchange="$('#price_total').html($(this).val()); $('#price_single').html( ( $(this).val() / $(this).find('option:selected').data('value') ).toFixed(2) );">
                        <option data-value="5" value="36.60">
                            <%= locale.get('tokens_5') %>
                        </option>
                        <option data-value="12" value="73.20">
                            <%= locale.get('tokens_10') %>
                        </option>
                        <option data-value="25" value="146.40">
                            <%= locale.get('tokens_20') %>
                        </option>
                        <!--<option data-value="10" value="50" selected>
                            10 tokens
                        </option>
                        <option data-value="20" value="100">
                            20 tokens
                        </option>
                        <option data-value="50" value="250">
                            50 tokens
                        </option>
                        <option data-value="100" value="475">
                            100 tokens
                        </option>
                        <option data-value="200" value="900">
                            200 tokens
                        </option>
                        <option data-value="500" value="1875">
                            500 tokens
                        </option>
                        <option data-value="1000" value="2500">
                            1000 tokens
                        </option>-->
                    </select>
                </div>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('buy_tokens_email') %>
                    </label>

                    <input id="email" type="text" />
                </div>

                <div id="price" class="tokens_price">
                    <%= locale.get('total') %>: €<span id="price_total">36.60</span> <!--(€<span id="price_single">5.00</span> <%= locale.get('each') %>)-->
                </div>

                <!--<form method="post" action="https://api-3t.sandbox.paypal.com/nvp" id="paypal">
                    <input type=hidden name="USER" value="sdk-three_api1.sdk.com">
                    <input type=hidden name="PWD" value="QFZCWN5HZM8VBG7Q">
                    <input type=hidden name="SIGNATURE" value="A-IzJhZZjhg29XQ2qnhapuwxIDzyAZQ92FRP5dqBzVesOkzbdUONzmOU">
                    <input type=hidden name="VERSION" value="119.0">
                    <input type=hidden name="PAYMENTREQUEST_0_PAYMENTACTION" value="Sale">
                    <input type=hidden name="PAYMENTREQUEST_0_AMT" value="">
                    <input type=hidden name="RETURNURL" value="http://client.stinjee.com/api/paypal/transaction/success">
                    <input type=hidden name="CANCELURL" value="http://client.stinjee.com/api/paypal/transaction/cancel">
                    <input type=hidden name="METHOD" value="SetExpressCheckout">
                </form>-->

                <div style="text-align: center;">
                    <a href="#" class="button pay" onclick="return false"><%= locale.get('pay') %></a>
                </div>
            </div>

            <div id="country_section" style="display: none;">
                <div class="tokens_num">
                    <label>
                        <%= locale.get('country') %>
                    </label>

                    <select id="country">
                        <%
                        _.each(countries, function(item, key, list) {
                        %>
                        <option value="<%= key %>" <% if (key == locale.getCountry().toUpperCase()) { %> selected <% } %> ><%= locale.get(item) %></option>
                        <%
                        });
                        %>
                    </select>
                </div>

                <div style="margin-left: 200px;">
                    <a href="#" class="button country_section-back" onclick="return false"><%= locale.get('back') %></a>
                    <a href="#" class="button pay" onclick="return false"><%= locale.get('pay') %></a>
                </div>
            </div>

            <div id="nopayment_section" style="display: none">
                <div class="bulk_upload_text" style="margin-bottom: 30px;">
                    <%= locale.get('nopayment_text') %>
                </div>

                <div style="text-align: center;">
                    <a href="#" class="button pay"><%= locale.get('back') %></a>
                </div>
            </div>

            <div style="text-align: center;">
                <br/>
                <br/>
                <div class="paypal">
                    <span><%= locale.get('powered_by') %></span>
                    <!--[if (!IE)|(gt IE 8)]> -->
                    <img src="/i/paypal_rgb.svg" alt="paypal" />
                    <!-- <![endif]-->
                    <!--[if lte IE 9]>
                    <img src="/i/png/paypal.png" alt="paypal" style="width: 179px;" />
                    <![endif]-->

                </div>
            </div>
        </div>
    </script>


    <script type="text/template" id="PaymentErrorTemplate">
        <div class="bulk_results buy_tokens">

            <div class="list_header">
                <%= locale.get('payment_error') %>
            </div>

            <div class="bulk_upload_text" style="margin-bottom: 30px;">
                <%= locale.get(message) %>

                <br/><br/>

                <%= locale.get('error_text') %>
            </div>

            <div style="text-align: center; padding-top: 20px;">
                <a href="#!/get_more_tokens" class="button"><%= locale.get('back') %></a>
            </div>
        </div>
    </script>

    <script type="text/template" id="BulkTemplate">
        <div class="bulk_results">

            <div class="list_header">
                <%= locale.get('bulk_upload_results') %>
            </div>

            <% if (!data.success.length) { %>
            <center><%= locale.get('no_specials_imported') %></center>
            <br/><br/><br/>
            <% } %>

            <% if (data.success.length) { %>
            <div class="batch_window batch_tokens bulk_success" style="display: block">
                <div class="batch_tokens_title">
                    <%= locale.get('specials_added') %>
                </div>

                <% _.each(data.success, function(item) { %>
                <span><%= item || locale.get('not_specified') %></span>
                <% }); %>
            </div>
            <% } %>

            <% _.each(data.errors, function(arr, n) { %>

            <% if (arr.length) { %>
            <div class="batch_window batch_tokens" style="display: block">
                <div class="batch_tokens_title">
                    <%= locale.get('specials_not_added_' + n) %>
                </div>

                <% _.each(arr, function(item) { %>
                <span><%= item || locale.get('not_specified') %></span>
                <% }); %>
            </div>
            <% } %>

            <% }); %>

            <div style="text-align: center;">
                <a href="/" class="button" style="margin-top: 60px;display: inline-block; padding: 8px 20px;border-radius: 4px;"><%= locale.get('ok_go_back') %></a>
            </div>

        </div>
    </script>

    <script type="text/template" id="BulkProgressTemplate">
        <div class="bulk_results">
            <div class="list_header">
                <%= locale.get('bulk_upload_progress') %>
            </div>
            <div style="text-align: center;">
                <%= locale.get('proccessed') %>: <span id="proccessed">0</span> / <span id="all">0</span>; <%= locale.get('success') %>: <span id="success">0</span>; <%= locale.get('errors') %>: <span id="errors">0</span>;
                <br/>
                <div id="proggress"></div>
                <br/><br/>
                <img src="/i/small_loader.gif" style="margin: 35px 20px 6px;" />
            </div>
        </div>
    </script>

    <script type="text/template" id="BatchTemplate">
        <div class="list">
            <!--<div class="start_title">
                <%= locale.get('do_not_forget_these_tokens', [batch.tokens.length]) %>
            </div>
            <div class="batch_window batch_tokens" style="display: block;">
                <div class="batch_item" id="<%= batch.id %>">
                        <div class="specials_name" style="width: 280px; color: #75ad45;">
                            <%= locale.get('batch_id') %>: <%= batch.id %>
                        </div>
                        <div class="separator"></div>
                        <div class="specials_created_at">
                            <%= locale.get('created_at') %> <%= moment(batch.createdAt.iso, 'YYYY-MM-DD\THH:mm:ss.SSS\Z').format('D MMMM YYYY') %>
                        </div>
                        <div class="separator"></div>
                        <div class="specials_tokens" >
                                <%= batch.tokens.length %> <%= locale.get('active_tokens') %>
                        </div>
                        <div class="specials_full_info" onclick="event.stopPropagation();" style="display: block;">
                            <% if (batch.tokens.length) { %>
                                <% _.each(batch.tokens, function(item) { %>
                                    <span><%= item %></span>
                                <% }); %>
                            <% } %>

                            <div class="info_buttons">
                                <a href="/csv/tokens/<%= batch.id %>?purchased=1&transaction=<%= transaction %>" class="right" target="_blank"><%= locale.get('get_csv') %></a>
                                    <a href="#" class="right blue print" onclick="return false"><%= locale.get('print') %></a>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="start_title">
                <%= locale.get('succesfull_payment_title', [batch.tokens.length]) %>
            </div>

            <br/>
            <div style="text-align: center;">
                <a href="/csv/tokens/<%= batch.id %>?purchased=1&transaction=<%= transaction %>" target="_blank" class="button"><%= locale.get('download') %></a>
            </div>

            <br/><br/><br/>
            <div class="start_title">
                <%= locale.get('invoice_details_title') %>
            </div>-->

            <div class="list_header">
                <%= locale.get('succesfull_payment_title') %>
                <br/><br/>
                <span style="font-size: 14px; font-family: Roboto-Light; padding: 0 92px; display: inline-block;"><%= locale.get('invoice_details_title') %></span>
            </div>

            <div id="start_section" >

                <% if (!transaction.updated) { %>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('email') %>
                    </label>

                    <input id="email" type="text" readonly value="<%= transaction.email %>" />
                </div>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('invoice_company_name') %><span>*</span>
                    </label>

                    <input id="company" type="text" value="<%= transaction.company %>" />
                </div>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('invoice_store_name') %>
                    </label>

                    <input id="store" type="text" value="<%= transaction.store %>"  />
                </div>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('address') %><span>*</span>
                    </label>

                    <input id="address" type="text"  value="<%= transaction.address %>"  />
                </div>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('city') %><span>*</span>
                    </label>

                    <input id="city" type="text"  value="<%= transaction.city %>" />
                </div>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('province') %><span>*</span>
                    </label>

                    <input id="province" type="text"  value="<%= transaction.province %>"  />
                </div>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('postal_id') %><span>*</span>
                    </label>

                    <input id="postal_id" type="text"  value="<%= transaction.postal_id %>"  />
                </div>

                <div class="tokens_num">
                    <label>
                        <%= locale.get('fiscal_id') %><span>*</span>
                    </label>

                    <input id="fiscal_id" type="text"  value="<%= transaction.fiscal_id %>"  />
                </div>

                <div class="tokens_num" style="margin-bottom: -50px;">
                    <label style="width: 200px;">
                        <br/>
                        <br/>
                        <span>*</span> <%= locale.get('field_requested') %>
                    </label>
                </div>

                <div style="text-align: center;">
                    <a href="#" class="button pay" onclick="return false"><%= locale.get('send') %></a>
                </div>


                <% } %>
            </div>


        </div>
    </script>
    <script type="text/template" id="InvoiceSuccessTemplate">
        <div class="list">

            <div class="list_header">
                <%= locale.get('invoice_success_title') %>
            </div>

            <div id="start_section">
                <div class="bulk_upload_text" style="margin-bottom: 30px; text-align: center;">
                    <%= locale.get('invoice_success_text') %>
                    <br/><br/>
                    <%= locale.get('succesfull_payment_text') %>
                </div>

                <br/>
                <div style="text-align: center;">
                    <a href="/csv/tokens/<%= batch.id %>?purchased=1&transaction=<%= transaction.objectId %>" target="_blank" class="button" style="padding: 12px 19px; font-size: 12px; text-transform: uppercase;"><%= locale.get('download') %></a>
                </div>
            </div>

        </div>
    </script>
</head>
<body>
<div id="font_loader">Stin Jee</div>
<div class="header" id="header"></div>
<div id="content">
    <img src="data:image/gif;base64,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" style="position: absolute; top: 50%; left: 50%; margin-top: -33px; margin-left: -33px; display: block;">
</div>
</body>
<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', 'UA-53068280-1', 'auto');ga('send', 'pageview');</script>
</html>