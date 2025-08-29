XE DEVELOPER CENTER
====================

특징
-------------------
 XE 에 O-Auth 2.0 로그인과 RESTFul API 를 지원합니다.

요구사항
-------------------
  * PHP 7.0+
  * OpenSSL
  * XpressEngine 1.9.6+
  * composer
  * redis (optional)
  
라이센스
-------------------
  해당 프로그램은 MIT 라이센스를 따릅니다.  
  
TODO
-------------------
  * 메세지 혹은 댓글 작성시 알림 기능 지원
  * 실시간 API 
  
공통 에러
-------------------
  * 400 - unsupported_grant_type
    * 해당 프로그램은 Authorization Code Grant 와 Refresh Token Grant 만 지원합니다.
    * 다른 grant type 으로 요청시 출력됩니다.
  * 400 - invalid_client_id
    * client_id 가 누락 혹은 존재하지 않을 경우 출력됩니다.
  * 400 - invalid_code
    * auth_code 가 잘못되거나 만료 혹은 revoke 되었을 경우 출력됩니다.
  * 400 - invalid_redirect_uri
    * callback url 이 잘못되었을 경우 출력됩니다.
  * 400 - invalid_refresh_token
    * refresh token 이 잘못되었을 경우 출력됩니다.
  * 400 - invalid_scope
    * 권한 (scope) 가 잘못되었을 경우 출력됩니다.
  * 401 - invalid_client
    * client_id 가 잘못되었을 경우 출력됩니다.
  * 401 - invalid_request
    * refresh token 이 잘못되거나 만료 혹은 revoke 되었을 경우 출력됩니다.
  * 401 - access_denied
    * access token 이 잘못되거나 만료 혹은 revoke 되었을 경우 출력됩니다.
  * 401 - permission_denied
    * 해당 리소스에 접근하지 못할 경우 출력됩니다.
  * 404 - resource is gone
    * 해당 리소스가 존재하지 않을때 출력됩니다.
  * 405 - method_not_allowed
    * 지원하지 않는 Request Method
    * API End Point 에 허용되지 않는 Method 로 접근시 출력됩니다.
  * 406 - module_not_exists
    * 연관 모듈이 존재하지 않을 경우 출력됩니다.    
  * 429 - User request limit reached
    * 허용하는 범위의 요청이 넘어갔을 경우 출력됩니다.    
    
API Reference
-------------------
### 약관동의
API 를 사용하기 위해서 먼저 해당 앱의 이용동의가 필요합니다.
이용동의를 받는 유저의 개인정보만 사용할 수 있습니다.

**GET /?module=devcenter&act=dispDevcenterAuthorize&client_id=[앱이름]&state=[state]**

_Request_
  * client_id - 필수 / 앱 아이디 입니다.
  * state - 옵션 / 약관 동의 후 callback url 로 이동시 같이 전달됩니다. 이 값을 통해 CSRF 공격을 방어할 수 있습니다.


### Authorization Code Grant
약관 동의 후 Authorization Code 와 함께 Callback URL 로 이동하게 됩니다.
이때 Auth Code 를 전달하면 Access Token / Refresh Token 을 전달 받을 수 있습니다.
authorization code 는 10분의 유효시간을 가집니다. 

**POST /?module=devcenter&act=token**

_Request_
  * client_id - 필수 / 앱 아이디 입니다.
  * client_secret - 필수 / API 비밀키 입니다.
  * redirect_uri - 필수 / callback url 을 지정합니다.
  * grant_type - 필수 / 인증 타입을 지정합니다. authorization_code 로 입력합니다.
  * code - 필수 / 리다이렉션으로 넘어온 authorization code 를 입력합니다.

_Response_
    
    {
        "token_type":"Bearer",
        "expires_in":7200,
        "access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImVlNzhjZWViZWNjMTQ2NGRkNmNlODk2OGJmNzFjNzVlMzkxZjNjNTQ4ZTRkMjYyN2YxOTRmNGNiZmFmYzY5MTNiZGJjMmFlMGQwYjcwZDAwIn0.eyJhdWQiOiIwMDA0Ni1DMEpCNS1YOUxGRi03NldOWS1ZQVFMNSIsImp0aSI6ImVlNzhjZWViZWNjMTQ2NGRkNmNlODk2OGJmNzFjNzVlMzkxZjNjNTQ4ZTRkMjYyN2YxOTRmNGNiZmFmYzY5MTNiZGJjMmFlMGQwYjcwZDAwIiwiaWF0IjoxNTIzOTQyMzc4LCJuYmYiOjE1MjM5NDIzNzgsImV4cCI6MTUyMzk0OTU3OCwic3ViIjoiMTUwIiwic2NvcGVzIjpbInJlYWQiLCJ3cml0ZSJdfQ.GBYXav59z7xnTz2_SpqtzFvvtLPy2hl69I06E_tZQ9ZeqnS5mk7aA_2DvIjSgoKsoOXfMKgBl-7se7VGAkDFopcNb3rwimpUt6pXoEQKHNSVwKZqNQ09R0tCPuP8DcUdoAt27QLnwPyDDJnRg7L6Bd3EIKhjLGdwfJlyz-2YbZp8VzluaRDqYrBn8MYdPjUqoZ5_E26btwSj-2ZVl-ucGB-4OlqiZ5lwgCUmvoOJAz4BB5Q1bJ9BH8iPAfdofpQhqStg5kJuq1RodXQz2_ZcpKaTNT5Fe8f1bEnmE4dEHEJuMZ767DNaD8quJCo2VeiCeNo8MU4kA9mcP6XhivugBw",
        "refresh_token":"def50200d7bcb8b8882dd90876e5507bfa467a510de0447bd21742ead737e3915d08c698ba09417164eb28b737f654270e2806e9a0621417c4d8f57b2919ed6fcc26b285811b4bf3b3ec4edc93cd7976bf691fa00d8bbf74054fedb4af7fb6b8335a9e62fa06555bc996c04b2f7431280e813409e9f0b4c56f30bf795582ed91742759719f03c9cdfa9af7538c4c905eea231441b4156f0e00daf36d8f96d87ec8f09b7d80071bf1082e2f7cf5f7a821dc05ce9e778ad29b5bcc87d1b4c06e80004ea61bffeba417b1e5fee3b9ca191549789c60e5682476ea8149bb2aa1dbb83c1c26b74017b17a4bfec0c3dff0d0a936d307531cd2dc1bcf09bd821fa32ab2824ce1e2df72a97d0c45caf09a2d2359ee9830017ed3c699d37c6899affd5ab65211005d1a0a5e07c8839e848ac4c77d85ab4403ad8da43b1be37ea4184a2ac64469232678699832770d6b5e09d5ddf4fc4555f859ccd8efec6aa9ca5a7f6530854c5a549e16c6234b752a9f8c43e18675e493f84be463fcaa835b61f28d31b6971ca835994382082d654cb18e"
    }
  
  * token_type - 토큰 타입이 리턴됩니다.
  * expires_in - access token 만료시간이 리턴됩니다.
  * access_token - access token 입니다.
  * refresh_token - refresh token 입니다. 
  
### Refresh Token Grant
access token 은 2시간의 유효시간을 가집니다. 
유효시간이 지난 경우 refresh token 을 통해 갱신할 수 있습니다.
refresh token 은 1개월의 유효시간을 가집니다.  

**POST /?module=devcenter&act=token**

_Request_
  * client_id - 필수 / 앱 아이디 입니다.
  * client_secret - 필수 / API 비밀키 입니다.
  * grant_type - 필수 / 인증 타입을 지정합니다. refresh_token 으로 입력합니다.
  * refresh_token - 필수 / refresh token 을 입력합니다.

_Response_
    
    {
        "token_type":"Bearer",
        "expires_in":7200,
        "access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImVlNzhjZWViZWNjMTQ2NGRkNmNlODk2OGJmNzFjNzVlMzkxZjNjNTQ4ZTRkMjYyN2YxOTRmNGNiZmFmYzY5MTNiZGJjMmFlMGQwYjcwZDAwIn0.eyJhdWQiOiIwMDA0Ni1DMEpCNS1YOUxGRi03NldOWS1ZQVFMNSIsImp0aSI6ImVlNzhjZWViZWNjMTQ2NGRkNmNlODk2OGJmNzFjNzVlMzkxZjNjNTQ4ZTRkMjYyN2YxOTRmNGNiZmFmYzY5MTNiZGJjMmFlMGQwYjcwZDAwIiwiaWF0IjoxNTIzOTQyMzc4LCJuYmYiOjE1MjM5NDIzNzgsImV4cCI6MTUyMzk0OTU3OCwic3ViIjoiMTUwIiwic2NvcGVzIjpbInJlYWQiLCJ3cml0ZSJdfQ.GBYXav59z7xnTz2_SpqtzFvvtLPy2hl69I06E_tZQ9ZeqnS5mk7aA_2DvIjSgoKsoOXfMKgBl-7se7VGAkDFopcNb3rwimpUt6pXoEQKHNSVwKZqNQ09R0tCPuP8DcUdoAt27QLnwPyDDJnRg7L6Bd3EIKhjLGdwfJlyz-2YbZp8VzluaRDqYrBn8MYdPjUqoZ5_E26btwSj-2ZVl-ucGB-4OlqiZ5lwgCUmvoOJAz4BB5Q1bJ9BH8iPAfdofpQhqStg5kJuq1RodXQz2_ZcpKaTNT5Fe8f1bEnmE4dEHEJuMZ767DNaD8quJCo2VeiCeNo8MU4kA9mcP6XhivugBw",
        "refresh_token":"def50200d7bcb8b8882dd90876e5507bfa467a510de0447bd21742ead737e3915d08c698ba09417164eb28b737f654270e2806e9a0621417c4d8f57b2919ed6fcc26b285811b4bf3b3ec4edc93cd7976bf691fa00d8bbf74054fedb4af7fb6b8335a9e62fa06555bc996c04b2f7431280e813409e9f0b4c56f30bf795582ed91742759719f03c9cdfa9af7538c4c905eea231441b4156f0e00daf36d8f96d87ec8f09b7d80071bf1082e2f7cf5f7a821dc05ce9e778ad29b5bcc87d1b4c06e80004ea61bffeba417b1e5fee3b9ca191549789c60e5682476ea8149bb2aa1dbb83c1c26b74017b17a4bfec0c3dff0d0a936d307531cd2dc1bcf09bd821fa32ab2824ce1e2df72a97d0c45caf09a2d2359ee9830017ed3c699d37c6899affd5ab65211005d1a0a5e07c8839e848ac4c77d85ab4403ad8da43b1be37ea4184a2ac64469232678699832770d6b5e09d5ddf4fc4555f859ccd8efec6aa9ca5a7f6530854c5a549e16c6234b752a9f8c43e18675e493f84be463fcaa835b61f28d31b6971ca835994382082d654cb18e"
    }
  
  * token_type - 토큰 타입이 리턴됩니다.
  * expires_in - access token 만료시간이 리턴됩니다.
  * access_token - access token 입니다.
  * refresh_token - refresh token 입니다. 
  
### 프로필 불러오기 (타인의 개인정보 조회시 stream 권한 필요)

사용자의 프로필을 호출합니다.

**GET /?module=devcenter&act=profile**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * member_unique_srl - 옵션 / 타인의 개인정보를 조회할때 사용하는 키입니다.
  
_Response_

    {
        "member_unique_srl": "6jOJl1LBXtOl1lv6MqJBIv6fmf0aGexsvnlvjRPRdmQkyhqAQroaBEIDsUMUA9zUuCNbJ3",
        "joined_at": 1487744104,
        "allow_mailing": false,
        "allow_message": true,
        "email_address": "mas***@mono.svn.io",
        "user_id": "kyle_li",
        "nick_name": "신디로퍼",
        "blog": ""
    }
    
  * member_unique_srl - 고유한 개인키입니다.
  * joined_at - 가입일 입니다.
  * allow_mailing - 메일 허용 유무입니다.
  * allow_message - 메세지 허용 유무입니다.
  * email_address - 이메일 주소입니다.
  * user_id - 아이디입니다.
  * nick_name - 닉네임입니다.
  * blog - 블로그 주소입니다.
  
## 친구 불러오기 (message 권한 필요)

사용자의 친구를 호출합니다.

**GET /?module=devcenter&act=friend**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * page - 옵션 / 페이지 파라메터입니다.
  
_Response_

    {
        "count": 12,
        "current_page": "1",
        "total_page": 2,
        "entries": [
            {
                "member_unique_srl": "6jOJl1LCRuyxA1wyB6GaUAYJrM1EP5FN20fURYzRaK0371Uhn6kfKvG1y5HFes8G7eh9Nx",
                "nick_name": "김똘똘",
                "group_srl": 0,
                "group_title": null,
                "register_at": 1514655325
            },
            ...
        ]
    }
    
  * count - 총 row 수입니다.
  * current_page - 현재 page 입니다.
  * total_page - 총 page 수 입니다.
  * entries - 현재 row 가 담긴 배열입니다.
      * member_unique_srl - 고유한 개인키입니다.
      * nick_name - 닉네임입니다.
      * group_srl - 친구 그룹키입니다.
      * group_title - 친구 그룹 이름입니다.
      * register_at - 등록일 입니다.
      
## 친구 등록하기 (message 권한 필요) 

친구를 등록합니다.

**POST /?module=devcenter&act=friend**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * member_unique_srl - 필수 / 사용자의 고유한 개인키를 입력합니다.
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.
  
## 친구 삭제하기 (message 권한 필요)    

친구를 삭제합니다.

**DELETE /?module=devcenter&act=friend**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * member_unique_srl - 필수 / 사용자의 고유한 개인키를 입력합니다.
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.  
  
## 메세지 불러오기 (message 권한 필요)

사용자의 메세지를 호출합니다.

**GET /?module=devcenter&act=message**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * page - 옵션 / 페이지 파라메터입니다.
  * message_srl - 옵션 / 메세지 키 입니다. 지정시 내용을 읽어옵니다.
  * category - 옵션 / 메세지 타입입니다. 기본값은 receive (받은 메세지) 이며 send (보낸 메세지) / store (보관 메세지) 를 지정이 가능합니다.
  
_Response_

    {
        "count": 4,
        "current_page": "1",
        "total_page": 1,
        "entries": [
            {
                "message_srl": 3701750,
                "title": "님 잘생긴듯",
                "member_unique_srl": "6jOJl1LDLVnOseVBcMlgx7qy2d6B7lfrKZ4czpHoH62zJoobuQ2QQFLhy0OJpWKkjh1KI5",
                "nick_name": "님팬",
                "is_readed": true,
                "register_at": 1511598698,
                "readed_at": 1511607866
            },
            ...
        ]
    }
    
  * count - 총 row 수입니다.
  * current_page - 현재 page 입니다.
  * total_page - 총 page 수 입니다.
  * entries - 현재 row 가 담긴 배열입니다.
      * message_srl - 메세지 키 입니다.
      * title - 제목 입니다.
      * member_unique_srl - 고유한 개인키입니다.
      * nick_name - 닉네임입니다.
      * is_readed - 메세지 확인 유무입니다.
      * register_at - 등록일 입니다.
      * readed_at - 확인일 입니다.
      
## 메세지 쓰기 (message 권한 필요)

메세지를 등록합니다.

**POST /?module=devcenter&act=message&member_unique_srl=[memer_unique_srl]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * member_unique_srl - 필수 / 사용자의 고유한 개인키를 입력합니다.
  * title - 필수 / 메세지 제목 입니다.
  * content - 필수 / 메세지 내용 입니다.
  * allow_mail - 필수 / 메세지를 메일로 수신허용할 것인지 선택할 수 있습니다.
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.
  
## 메세지 삭제하기 (message 권한 필요)    

메세지를 삭제합니다.

**DELETE /?module=devcenter&act=message&message_srl=[message_srl]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * message_srl - 필수 / 메세지키를 입력합니다.
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.       
  
## 로그인 기록 불러오기 (read 권한 필요)

사용자의 로그인 기록을 호출합니다.
(loginlog 모듈이 설치되어 있어야 호출이 가능합니다.)

**GET /?module=devcenter&act=login_histories**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * page - 옵션 / 페이지 파라메터입니다.
  
_Response_

    {
        "count": 4456,
        "current_page": "1",
        "total_page": 149,
        "entries": [
            {
                "log_srl": 4768094,
                "address": "1.234.4.18",
                "is_success": true,
                "platform": "OAuth2",
                "browser": "ExCF for Smartphone",
                "register_at": 1523947627
            },
            ...
        ]
    }
    
  * count - 총 row 수입니다.
  * current_page - 현재 page 입니다.
  * total_page - 총 page 수 입니다.
  * entries - 현재 row 가 담긴 배열입니다.
      * log_srl - 메세지 키 입니다.
      * address - 아이피 주소입니다.
      * is_success - 로그인 성공 유무입니다.
      * platform - 플랫폼 입니다.
      * browser - 브라우져 입니다. (OAuth일 경우는 허용한 앱 이름)
      * register_at - 등록일 입니다.
      
## 사이트 메뉴 불러오기 (권한 불필요)

해당 사이트의 게시판 리스트들을 가져옵니다.

**GET /?module=devcenter&act=sitemap**

_Response_

    {
        "menuSrl": 65,
        "title": "메뉴",
        "description": null,
        "items": [
            {
                "text": "자게",
                "url": "freeboard",
                "is_shortcut": "N",
                "module": "board',
                "browser_title": "자유게시판",
            },
            ...
        ]
    }
    
  * menuSrl - 메뉴키 입니다.
  * title - 메뉴명입니다.
  * description - 메뉴에 대한 설명입니다.
  * items - 메뉴 아래 게시판과 페이지들이 담긴 배열입니다.
      * text - 게시판 명 입니다.
      * url - 게시판 주소입니다.
      * is_shortcut - 단순 링크인지 표기합니다.
      * module - 게시판 모듈인지 / 페이지 모듈인지 표기합니다.
      * browser_title - 브라우저 타이틀에 표시되는 내용을 표기합니다.

## 게시물 불러오기 (stream 권한 필요)

게시물을 볼러옵니다.
일반적으로 stream 권한이 필요하지만, 자신의 글을 읽을 경우에는 read 권한으로 충분합니다.

**GET /?module=devcenter&act=article**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * board - 옵션 / 게시판 아이디입니다. (board 나 article_srl 파라메터 하나는 필수 입니다.)
  * article_srl - 옵션 / 게시물 키 입니다.
  * page - 옵션 / 페이지 파라메터입니다.  
  
_Response_

    {
        "count": 202835,
        "current_page": 1,
        "total_page": 10142,
        "grant": {
            "is_site_admin": true,
            "manager": true,
            "access": true,
            "is_admin": true,
            "list": true,
            "view": true,
            "write_document": true,
            "write_comment": true,
            "consultation_read": true
        },
        "entries": [
            {
                "article_srl": 4767813,
                "title": "안녕하세요.",
                "nick_name": "이똘똘",
                "member_unique_srl": "6jOJl1LBXmY4mFDgzPmPXDlzXOttUy02z5PtFCTlJHzy60lFttaZMPNTOyyoYPDLxQx9nF",
                "member_profile_image": null,
                "member_signature": null,
                "comment_count": 0,
                "readed_count": 21,
                "voted_count": null,
                "is_notice": true,
                "register_at": 1518520846,
                "updated_at": 1518520858
            },
            ...
        ]
    }  

  * count - 총 row 수입니다.
  * current_page - 현재 page 입니다.
  * total_page - 총 page 수 입니다.
  * grant - 권한 내용 입니다.
      * is_site_admin - 사이트 관리자인지 표기합니다.
      * manager - 해당 게시판 관리 권한이 있는지 표기합니다.
      * access - 접근 가능한지 표기합니다.
      * is_admin - 그룹 관리자인지 표기합니다.
      * list - 리스트 보기 권한이 있는지 표기합니다.
      * view - 글 보기 권한이 있는지 표기합니다.
      * write_document - 글 쓰기 권한이 있는지 표기합니다.
      * write_comment - 댓글 달기 권한이 있는지 표기합니다.
      * consultation_read - 상담글 읽기 권한이 있는지 표기합니다.
  * entries - 현재 row 가 담긴 배열입니다.
      * article_srl - 게시물 키 입니다.
      * title - 제목 입니다.
      * nick_name - 닉네임입니다.
      * member_unique_srl - 고유한 개인키입니다.
      * member_profile_image - 프로필 이미지입니다.
      * member_signature - 회원 서명입니다.
      * comment_count - 댓글 수 입니다.
      * readed_count - 조회 수 입니다.
      * voted_count - 추천 수 입니다.
      * is_notice - 공지 유무입니다.
      * register_at - 등록일 입니다.
      * updated_at - 수정일 입니다.
      
## 게시물 쓰기 (write 권한 필요)

게시물을 등록합니다.

**POST /?module=devcenter&act=article&board=[board]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * board - 필수 / 게시판 ID 입니다.
  * title - 필수 / 게시물 제목 입니다.
  * content - 필수 / 게시물 내용 입니다.
  * allow_comment - 옵션 / 댓글 허용 유무입니다. 기본은 0 입니다.
  * allow_trackback - 옵션 / 트랙백 허용 유무입니다. 기본은 0 입니다.
  * allow_notify - 옵션 / 댓글 작성시 메세지 알림 허용 유무입니다. 기본은 0 입니다.
  * tags - 옵션 / 관련 태그를 작성합니다. 구분 기호는 , 입니다.  
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.      
  
## 게시물 수정 (write 권한 필요)

게시물을 수정합니다.

**PUT /?module=devcenter&act=article&article_srl=[article_srl]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * article_srl - 필수 / 게시물 키 입니다.
  * title - 필수 / 게시물 제목 입니다.
  * content - 필수 / 게시물 내용 입니다.
  * allow_comment - 옵션 / 댓글 허용 유무입니다. 기본은 0 입니다.
  * allow_trackback - 옵션 / 트랙백 허용 유무입니다. 기본은 0 입니다.
  * allow_notify - 옵션 / 댓글 작성시 메세지 알림 허용 유무입니다. 기본은 0 입니다.
  * tags - 옵션 / 관련 태그를 작성합니다. 구분 기호는 , 입니다.  
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.        
  
## 게시물 삭제하기 (write 권한 필요)    

게시물을 삭제합니다.

**DELETE /?module=devcenter&act=article&article_srl=[article_srl]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * article_srl - 필수 / 게시물 키 입니다.
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.     
  
## 댓글 불러오기 (stream 권한 필요)

댓글을 볼러옵니다.

**GET /?module=devcenter&act=comment**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * article_srl - 필수 / 게시물 키 입니다.
  * page - 옵션 / 페이지 파라메터입니다.  
  
_Response_

    {
        "count": 4,
        "current_page": 1,
        "total_page": 1,
        "entries": [
            {
                "comment_srl": 4767853,
                "article_srl": 4767847,
                "nick_name": "드래곤마스터",
                "member_unique_srl": "6jOJl1LBY6umrF39uda6LufXfB1ICt7ZYAnHFYnosBQmkGzbtNJLUP3NgN4LIQCUvXKYWX",
                "member_profile_image": null,
                "member_signature": null,
                "content": "<div class="xe_content">드래곤이랑 관계있는거 아닐까요</div>",
                "depth": 0,
                "voted_count": 0,
                "register_at": 1518521179,
                "updated_at": 1518521179
            },
            ...
        ]
    }  

  * count - 총 row 수입니다.
  * current_page - 현재 page 입니다.
  * total_page - 총 page 수 입니다.
  * entries - 현재 row 가 담긴 배열입니다.
      * comment_srl - 댓글 키 입니다.
      * article_srl - 게시물 키 입니다.
      * nick_name - 닉네임입니다.
      * member_unique_srl - 고유한 개인키입니다.
      * member_profile_image - 프로필 이미지입니다.
      * member_signature - 회원 서명입니다.
      * content - 댓글 내용 입니다.
      * depth - 대댓글 깊이 입니다.
      * voted_count - 추천 수 입니다.
      * register_at - 등록일 입니다.
      * updated_at - 수정일 입니다.  
      
## 댓글 쓰기 (write 권한 필요)

댓글을 등록합니다.

**POST /?module=devcenter&act=comment&article_srl=[article_srl]&parent_srl=[parent_srl]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * article_srl - 필수 / 게시물 키 입니다.
  * parent_srl - 옵션 / 상위 댓글 (대댓글 기능) 키 입니다.
  * content - 필수 / 댓글 내용 입니다.  
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.      
  
## 댓글 수정 (write 권한 필요)

댓글을 수정합니다.

**PUT /?module=devcenter&act=comment&article_srl=[article_srl]&comment_srl=[comment_srl]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * article_srl - 필수 / 게시물 키 입니다.
  * comment_srl - 필수 / 댓글 키 입니다.
  * content - 필수 / 댓글 내용 입니다.  
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.
  
## 댓글 삭제 (write 권한 필요)

댓글을 삭제합니다.

**DELETE /?module=devcenter&act=comment&article_srl=[article_srl]&comment_srl=[comment_srl]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * article_srl - 필수 / 게시물 키 입니다.
  * comment_srl - 필수 / 댓글 키 입니다. 
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.          
  
## 스크랩 글 불러오기 (read 권한 필요)

스크랩한 게시물을 볼러옵니다.

**GET /?module=devcenter&act=scrap**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * page - 옵션 / 페이지 파라메터입니다.  
  
_Response_

    {
        "count": 202835,
        "current_page": 1,
        "total_page": 10142,
        "entries": [
            {
                "article_srl": 4767847,
                "title": "백일섭이 뭡니까",
                "nick_name": "아이유",
                "member_unique_srl": "6jOJl1LBXtOl1lv6MqJBIv6fmf0aGexsvnlvjRPRdmQkyhqAQroaBEIDsUMUA9zUuCNbJ3",
                "member_profile_image": null,
                "member_signature": null,
                "comment_count": null,
                "readed_count": null,
                "voted_count": null,
                "is_notice": null,
                "register_at": 1522649834,
                "updated_at": null
            },
            ...
        ]
    }  

  * count - 총 row 수입니다.
  * current_page - 현재 page 입니다.
  * total_page - 총 page 수 입니다.
  * entries - 현재 row 가 담긴 배열입니다.
      * article_srl - 게시물 키 입니다.
      * title - 제목 입니다.
      * nick_name - 닉네임입니다.
      * member_unique_srl - 고유한 개인키입니다.
      * member_profile_image - 프로필 이미지입니다.
      * member_signature - 회원 서명입니다.
      * comment_count - 댓글 수 입니다.
      * readed_count - 조회 수 입니다.
      * voted_count - 추천 수 입니다.
      * is_notice - 공지 유무입니다.
      * register_at - 등록일 입니다.
      * updated_at - 수정일 입니다.  
      
## 스크랩 게시물 등록하기 (write 권한 필요)

스크랩 게시물을 등록합니다.

**POST /?module=devcenter&act=scrap&article_srl=[article_srl]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * article_srl - 필수 / 게시물 키 입니다.
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.
  
## 스크랩 게시물 삭제하기 (write 권한 필요)

스크랩 게시물을 삭제합니다.

**DELETE /?module=devcenter&act=scrap&article_srl=[article_srl]**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * article_srl - 필수 / 게시물 키 입니다.
  
_Response_

    {
        "status": true
    }
    
  * status - 요청 성공시 true 로 리턴됩니다.           
  
## 내 게시글 불러오기 (read 권한 필요)

내가 작성한 게시물을 볼러옵니다.

**GET /?module=devcenter&act=my_article**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * page - 옵션 / 페이지 파라메터입니다.  
  
_Response_

    {
        "count": 202835,
        "current_page": 1,
        "total_page": 10142,
        "entries": [
            {
                "article_srl": 4767847,
                "title": "백일섭이 뭡니까",
                "nick_name": "아이유",
                "member_unique_srl": "6jOJl1LBXtOl1lv6MqJBIv6fmf0aGexsvnlvjRPRdmQkyhqAQroaBEIDsUMUA9zUuCNbJ3",
                "member_profile_image": null,
                "member_signature": null,
                "comment_count": null,
                "readed_count": null,
                "voted_count": null,
                "is_notice": null,
                "register_at": 1522649834,
                "updated_at": null
            },
            ...
        ]
    }  

  * count - 총 row 수입니다.
  * current_page - 현재 page 입니다.
  * total_page - 총 page 수 입니다.
  * entries - 현재 row 가 담긴 배열입니다.
      * article_srl - 게시물 키 입니다.
      * title - 제목 입니다.
      * nick_name - 닉네임입니다.
      * member_unique_srl - 고유한 개인키입니다.
      * member_profile_image - 프로필 이미지입니다.
      * member_signature - 회원 서명입니다.
      * comment_count - 댓글 수 입니다.
      * readed_count - 조회 수 입니다.
      * voted_count - 추천 수 입니다.
      * is_notice - 공지 유무입니다.
      * register_at - 등록일 입니다.
      * updated_at - 수정일 입니다.    
      
## 내 댓글 불러오기 (read 권한 필요)

내가 작성한 댓글을 볼러옵니다.

**GET /?module=devcenter&act=comment**

_Request Header_
  * Authorization - 필수 / Bearer [access_token] 형식으로 보냅니다.
  
_Request_
  * page - 옵션 / 페이지 파라메터입니다.  
  
_Response_

    {
        "count": 4,
        "current_page": 1,
        "total_page": 1,
        "entries": [
            {
                "comment_srl": 4767853,
                "article_srl": 4767847,
                "nick_name": "드래곤마스터",
                "member_unique_srl": "6jOJl1LBY6umrF39uda6LufXfB1ICt7ZYAnHFYnosBQmkGzbtNJLUP3NgN4LIQCUvXKYWX",
                "member_profile_image": null,
                "member_signature": null,
                "content": "<div class="xe_content">드래곤이랑 관계있는거 아닐까요</div>",
                "depth": 0,
                "voted_count": 0,
                "register_at": 1518521179,
                "updated_at": 1518521179
            },
            ...
        ]
    }  

  * count - 총 row 수입니다.
  * current_page - 현재 page 입니다.
  * total_page - 총 page 수 입니다.
  * entries - 현재 row 가 담긴 배열입니다.
      * comment_srl - 댓글 키 입니다.
      * article_srl - 게시물 키 입니다.
      * nick_name - 닉네임입니다.
      * member_unique_srl - 고유한 개인키입니다.
      * member_profile_image - 프로필 이미지입니다.
      * member_signature - 회원 서명입니다.
      * content - 댓글 내용 입니다.
      * depth - 대댓글 깊이 입니다.
      * voted_count - 추천 수 입니다.
      * register_at - 등록일 입니다.
      * updated_at - 수정일 입니다.               