@extends('layout.notif')
@section('main')
    <tr>
        <td>
            @if ($messager->heroIcon())
                <p>
                    <img src="{{ $messager->heroIcon() }}"
                        style="width: 120px; max-width: 150px; height: auto; margin: auto; display: block; border-radius:50%">
                </p>
            @endif

            @if ($messager->greeting())
                <p>{!! $messager->greeting() !!},</p>
            @endif

            @if ($messager->headline())
                <h3>{{ $messager->headline() }}</h3>
            @endif

            @if ($messager->introlines())
                <p> {!! $messager->introlines() !!} </p>
            @endif

            @if ($messager->hasAction())
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                    <tbody>
                        <tr>
                            <td align="center">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <a href="{{ $messager->getAction('link') }}" target="_blank">
                                                    {!! $messager->getAction('text') !!}
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif
            @if ($messager->lastlines())
                <p> {!! $messager->lastlines() !!} </p>
            @endif

            @if ($messager->hasAction())
                <p style="font-size: x-small">
                    Si vous n'arrivez pas à cliquer sur le lien, vous pouvez le copier et le coller directement dans la
                    barre d'adresse
                    de
                    de votre navigateur. <br>
                    {{ $messager->getAction('link') }}
                </p>
            @endif

            @if ($messager->goodbye())
                <p> <small><em>{!! $messager->goodbye() !!}</em></small> </p>
            @endif
        </td>
    </tr>
@endsection

@section('footer')
    @if ($messager->footer())
        <!-- START FOOTER -->
        <div class="footer">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                {{--  <tr>
                <td class="content-block">
                  <span class="apple-link">Company Inc, 3 Abbey Road, San Francisco CA 94102</span>
                  <br> Don't like these emails? <a href="http://i.imgur.com/CScmqnj.gif">Unsubscribe</a>.
                </td>
              </tr> --}}
                <tr>
                    <td class="content-block powered-by">
                        <div class="signature">
                            <p style="text-align: left; margin-left:2em;"> L'équipe {{ env('APP_NAME') }}</p>
                            <div style="text-align:center !important;">
                                <p style="">Besoin d'aide ?</p>
                                <p style="line-height:120%;">Prière nous contacter ici <a
                                        href="mailto:{{ env('ASSISTANCE_MAIL') }}"
                                        style="color:gray;"><u>{{ env('ASSISTANCE_MAIL') }}</u></a>
                                </p>
                            </div>
                        </div>

                    </td>
                </tr>
            </table>
        </div>
        <!-- END FOOTER -->
    @endif
@endsection
