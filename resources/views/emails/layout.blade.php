<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FunnyNodes')</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FDFBF7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #000000;">
    
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FDFBF7; padding: 40px 20px;">
        <tr>
            <td align="center">
                
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%;">
                    
                    {{-- Логотип --}}
                    <tr>
                        <td align="center" style="padding-bottom: 32px;">
                            <a href="{{ config('app.url') }}" style="text-decoration: none; display: inline-block;">
                                <img src="{{ config('app.url') }}/logo-full.jpg" alt="FunnyNodes" width="200" style="display: block; max-width: 200px; height: auto;">
                            </a>
                        </td>
                    </tr>
                    
                    {{-- Карточка --}}
                    <tr>
                        <td style="background-color: #FFFFFF; border: 3px solid #000000; box-shadow: 6px 6px 0 #000000;">
                            
                            {{-- Заголовок --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 32px 40px 16px 40px; border-bottom: 3px solid #000000;">
                                        <h1 style="margin: 0; font-size: 22px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #000000;">
                                            @yield('heading', 'FunnyNodes')
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                            
                            {{-- Контент --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 32px 40px;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                    
                    {{-- Футер --}}
                    <tr>
                        <td align="center" style="padding-top: 32px;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #666666;">
                                Это автоматическое письмо, не отвечайте на него.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #666666;">
                                <a href="{{ config('app.url') }}" style="color: #FF4911; text-decoration: none; font-weight: 700;">
                                    funny-code.space
                                </a>
                                &nbsp;·&nbsp;
                                <a href="https://t.me/sosnin_451" style="color: #FF4911; text-decoration: none; font-weight: 700;">
                                    Telegram
                                </a>
                            </p>
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>