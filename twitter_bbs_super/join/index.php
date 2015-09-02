<?php
    // 外部phpファイルの読み込み
    // 大きなサービスになってくると、機能ごとにファイルをわけ、
    // 必要なタイミングでrequireする実装が多くなります。
    require('../dbconnect.php');

    session_start();

    // フォームのバリデーション (エラー分岐処理)
    if (!empty($_POST)) { // isset($_POST)
        // nameのinputタグが空だった場合
        if ($_POST['name'] == '') {
            $errors['name'] = 'blank';
        }

        // emailのinputタグが空だった場合
        if ($_POST['email'] == '') {
            $errors['email'] = 'blank';
        }

        // passwordのinputタグが空だった場合
        if ($_POST['password'] == '') {
            $errors['password'] = 'blank';
        }

        // passwordの文字数制限4文字以上
        if (strlen($_POST['password']) < 4) {
            $errors['password'] = 'length';
        }

        // 画像ファイルはjpgもしくはgifでないとだめ
        $fileName = $_FILES['image']['name'];
        if (!empty($fileName)) {
            // substr(string, start)関数で、
            // 指定した文字列の指定したスタート地点からの文字列のみ取得
            $ext = substr($fileName, -3);
            if ($ext != 'jpg' && $ext != 'gif') {
              $errors['image'] = 'type';
            }
        }

        // 重複アカウントのチェック
        if (empty($errors)) {
            $sql = sprintf('SELECT COUNT(*) AS cnt FROM members WHERE email="%s"',
              mysqli_real_escape_string($db, $_POST['email'])
            );
            $record = mysqli_query($db, $sql) or die(mysqli_error($db));
            $table = mysqli_fetch_assoc($record);
            if ($table['cnt'] > 0) {
              $errors['email'] = 'duplicate';
            }
        }

        // エラーがなければ、チェック画面へ遷移
        if (empty($errors)) {
            // 画像をアップロードします
            // 保存する画像につける名前を決めて$image変数に格納
            $image = date('YmdHis') . $_FILES['image']['name'];

            // アップロード処理本体 move_uploaded_file()関数を使います
            move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/' . $image);

            // /member_pictureディレクトリは、ブラウザ上からアクセスできる権限が必要です。
            // sudo chmod -R 777 /var/www/html/twitter_bbs/member_picture

            $_SESSION['join'] = $_POST;
            $_SESSION['join']['image'] = $image;
            header('Location: check.php');
            exit();
        }
    }

    if ($_REQUEST['action'] == 'rewrite') {
      $_POST = $_SESSION['join'];
      $errors['rewrite'] = true;
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>会員登録</title>
</head>
<body>
  <h1>会員登録</h1>
  <p>次のフォームに必要事項をご記入下さい。</p>
  <!-- 自分自身にデータを送信したい場合はactionを空にしても良い -->
  <!-- 画像ファイルなどを送信する際は、enctypeが必要 -->
  <form action="" method="post" enctype="multipart/form-data">
    <dl>
      <dt>ニックネーム</dt>
      <dd>
        <input type="text" name="name" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['name'], ENT_QUOTES,'UTF-8'); ?>">
        <?php if ($errors['name'] == 'blank'): ?>
          <p class="error">* ニックネームを入力してください</p>
        <?php endif; ?>
      </dd>

      <dt>メールアドレス</dt>
      <dd>
        <input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['email'], ENT_QUOTES,'UTF-8'); ?>">
        <?php if ($errors['email'] == 'blank'): ?>
          <p class="error">* メールアドレスを入力してください</p>
        <?php endif; ?>
        <?php if ($errors['email'] == 'duplicate'): ?>
          <p class="error">* 指定されたメールアドレスはすでに登録されています</p>
        <?php endif; ?>
      </dd>

      <dt>パスワード</dt>
      <dd>
        <input type="password" name="password" size="10" maxlength="20" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES,'UTF-8'); ?>">
        <?php if ($errors['password'] == 'blank'): ?>
          <p class="error">* パスワードを入力してください</p>
        <?php endif; ?>

        <?php if ($errors['password'] == 'length'): ?>
          <p class="error">* パスワードは4文字以上で入力してください</p>
        <?php endif; ?>
      </dd>

      <dt>写真など</dt>
      <dd>
        <input type="file" name="image" size="35" value="test">
        <?php if ($errors['image'] == 'type'): ?>
          <p class="error">写真などは「jpg」または「gif」の画像を指定してください</p>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
          <p class="error">恐れ入りますが、画像を改めて指定してください</p>
        <?php endif; ?>
      </dd>
    </dl>
    <div><input type="submit" value="入力内容を確認する"></div>
  </form>
</body>
</html>
