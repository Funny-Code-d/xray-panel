@extends('emails.layout')

@section('title', 'Пароль изменён')
@section('heading', 'Пароль изменён')

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6;">
        Здравствуйте, <strong>{{ $user->first_name }}</strong>!
    </p>

    <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6;">
        Ваш пароль в FunnyNodes был успешно изменён.
    </p>

    {{-- Информация --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FDFBF7; border: 2px solid #000000; margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px 20px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="padding: 4px 0; font-size: 14px; color: #666666;">Дата:</td>
                        <td style="padding: 4px 0; font-size: 14px; font-weight: 700; text-align: right;">
                            {{ now()->format('d.m.Y H:i') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 14px; color: #666666;">IP-адрес:</td>
                        <td style="padding: 4px 0; font-size: 14px; font-weight: 700; text-align: right;">
                            {{ request()->ip() }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Предупреждение --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FEF2F2; border: 2px solid #DC2626; margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px 20px;">
                <p style="margin: 0; font-size: 14px; font-weight: 700; color: #DC2626;">
                    ⚠️ Если вы не меняли пароль — немедленно свяжитесь с администратором.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #666666;">
        Если у вас возникли проблемы — напишите нам в
        <a href="https://t.me/sosnin_451" style="color: #FF4911; text-decoration: none; font-weight: 700;">Telegram</a>.
    </p>
@endsection