@extends('emails.layout')

@section('title', 'Сброс пароля')
@section('heading', 'Сброс пароля')

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6;">
        Здравствуйте, <strong>{{ $user->first_name }}</strong>!
    </p>

    <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6;">
        Вы запросили сброс пароля в FunnyNodes. Нажмите на кнопку ниже, чтобы создать новый пароль.
    </p>

    {{-- Кнопка --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
        <tr>
            <td align="center">
                <a href="{{ $url }}" style="display: inline-block; padding: 14px 32px; background-color: #FFD700; color: #000000; border: 3px solid #000000; box-shadow: 4px 4px 0 #000000; text-decoration: none; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; font-size: 14px;">
                    Сбросить пароль
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #666666;">
        Или скопируйте ссылку в браузер:
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FDFBF7; border: 2px solid #000000; margin-bottom: 24px;">
        <tr>
            <td style="padding: 12px 16px;">
                <p style="margin: 0; font-size: 12px; font-family: 'Courier New', monospace; word-break: break-all; color: #000000;">
                    {{ $url }}
                </p>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #666666;">
        Ссылка действительна <strong>60 минут</strong>.
    </p>

    <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #666666;">
        Если вы не запрашивали сброс — просто проигнорируйте это письмо.
    </p>
@endsection