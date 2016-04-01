import js2py
javascript_pie_code = open('./javascript/pie.js')
js2py.eval_js(javascript_pie_code.read())

# print js2py.translate_js(javascript_pie_code.read())
